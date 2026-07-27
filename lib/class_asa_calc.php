<?php

/**
 * Calculo puro do boleto do Banco ASA SCD (594).
 * Sem banco de dados, sem sessao, sem saida — para poder ser testado isoladamente.
 * Regras: Kit Implantacao Cobranca Vinculada, manuais de boleto e de numero bancario.
 */
class asa_calc {

    const BANCO = '594';
    const MOEDA = '9';

    /**
     * Modulo 10 da FEBRABAN: pesos 2,1,2,1... da direita para a esquerda,
     * somando os digitos dos produtos maiores que 9.
     */
    public static function modulo_10($num) {
        $soma  = 0;
        $fator = 2;
        for ($i = strlen($num); $i > 0; $i--) {
            $produto = ((int) substr($num, $i - 1, 1)) * $fator;
            if ($produto > 9) {
                $produto = ((int) ($produto / 10)) + ($produto % 10);
            }
            $soma += $produto;
            $fator = ($fator == 2) ? 1 : 2;
        }
        $resto = $soma % 10;
        return ($resto == 0) ? 0 : 10 - $resto;
    }

    /**
     * Modulo 11 do codigo de barras: pesos 2 a 9 ciclicos da direita para a
     * esquerda. DV = 11 - resto, sendo 1 quando o resto e 0, 1 ou 10.
     */
    public static function modulo_11_barras($num) {
        $soma  = 0;
        $fator = 2;
        for ($i = strlen($num); $i > 0; $i--) {
            $soma += ((int) substr($num, $i - 1, 1)) * $fator;
            $fator = ($fator == 9) ? 2 : $fator + 1;
        }
        $resto = $soma % 11;
        if ($resto == 0 || $resto == 1 || $resto == 10) {
            return 1;
        }
        return 11 - $resto;
    }

    /**
     * DV do nosso numero: modulo 10 sobre agencia(4) + carteira(3) + nosso numero(10).
     * O manual do ASA traz variantes em modulo 11 para outras carteiras; se o banco
     * indicar uma delas, e este o unico metodo a mudar.
     */
    public static function dv_nosso_numero($agencia, $carteira, $nosso_numero) {
        $base = str_pad($agencia, 4, '0', STR_PAD_LEFT)
              . str_pad($carteira, 3, '0', STR_PAD_LEFT)
              . str_pad($nosso_numero, 10, '0', STR_PAD_LEFT);
        return self::modulo_10($base);
    }

    public static function nosso_numero_com_dv($agencia, $carteira, $nosso_numero) {
        return str_pad($nosso_numero, 10, '0', STR_PAD_LEFT)
             . self::dv_nosso_numero($agencia, $carteira, $nosso_numero);
    }

    /** Campo livre (25): agencia(4) + carteira(3) + operacao(7) + nosso numero com DV(11). */
    public static function campo_livre($agencia, $carteira, $operacao, $nosso_numero) {
        return str_pad($agencia, 4, '0', STR_PAD_LEFT)
             . str_pad($carteira, 3, '0', STR_PAD_LEFT)
             . str_pad($operacao, 7, '0', STR_PAD_LEFT)
             . self::nosso_numero_com_dv($agencia, $carteira, $nosso_numero);
    }

    /** Valor em centavos, zero-preenchido a esquerda. Evita erro de ponto flutuante. */
    public static function valor_centavos($valor, $tamanho) {
        $normalizado = number_format((float) $valor, 2, '.', '');
        return str_pad(str_replace('.', '', $normalizado), $tamanho, '0', STR_PAD_LEFT);
    }

    private static function data_para_dias($ano, $mes, $dia) {
        $seculo = substr($ano, 0, 2);
        $ano    = substr($ano, 2, 2);
        if ($mes > 2) {
            $mes -= 3;
        } else {
            $mes += 9;
            if ($ano) {
                $ano--;
            } else {
                $ano = 99;
                $seculo--;
            }
        }
        return floor((146097 * $seculo) / 4)
             + floor((1461 * $ano) / 4)
             + floor((153 * $mes + 2) / 5)
             + $dia + 1721119;
    }

    /**
     * Fator de vencimento FEBRABAN. A partir de 22/02/2025 o ciclo reinicia em 1000,
     * conforme o comunicado FB-082/2012. Data no formato dd/mm/aaaa.
     */
    public static function fator_vencimento($data_dmy) {
        $partes = explode('/', $data_dmy);
        $dias   = self::data_para_dias($partes[2], $partes[1], $partes[0]);
        if ($dias <= 2460728) {
            $fator = abs(self::data_para_dias('1997', '10', '07') - $dias);
        } else {
            $fator = abs(self::data_para_dias('2025', '02', '22') - $dias) + 1000;
        }
        return str_pad($fator, 4, '0', STR_PAD_LEFT);
    }

    public static function dv_codigo_barras($campo_livre, $fator, $valor10) {
        return self::modulo_11_barras(self::BANCO . self::MOEDA . $fator . $valor10 . $campo_livre);
    }

    /** Codigo de barras (44): banco(3) + moeda(1) + DV(1) + fator(4) + valor(10) + campo livre(25). */
    public static function codigo_barras($campo_livre, $fator, $valor10) {
        return self::BANCO . self::MOEDA
             . self::dv_codigo_barras($campo_livre, $fator, $valor10)
             . $fator . $valor10 . $campo_livre;
    }

    /** Linha digitavel FEBRABAN em cinco campos, com DV modulo 10 nos tres primeiros. */
    public static function linha_digitavel($campo_livre, $fator, $valor10, $dv_barras) {
        $campo1 = self::BANCO . self::MOEDA . substr($campo_livre, 0, 5);
        $campo1 .= self::modulo_10($campo1);

        $campo2 = substr($campo_livre, 5, 10);
        $campo2 .= self::modulo_10($campo2);

        $campo3 = substr($campo_livre, 15, 10);
        $campo3 .= self::modulo_10($campo3);

        return substr($campo1, 0, 5) . '.' . substr($campo1, 5)
             . ' ' . substr($campo2, 0, 5) . '.' . substr($campo2, 5)
             . ' ' . substr($campo3, 0, 5) . '.' . substr($campo3, 5)
             . ' ' . $dv_barras
             . ' ' . $fator . $valor10;
    }
}
