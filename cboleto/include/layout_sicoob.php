  <?php
  /*
  // +----------------------------------------------------------------------+
  // | BoletoPhp - Versão Beta                                              |
  // +----------------------------------------------------------------------+
  // | Este arquivo está disponível sob a Licença GPL disponível pela Web   |
  // | em http://pt.wikipedia.org/wiki/GNU_General_Public_License           |
  // | Você deve ter recebido uma cópia da GNU Public License junto com     |
  // | esse pacote; se não, escreva para:                                   |
  // |                                                                      |
  // | Free Software Foundation, Inc.                                       |
  // | 59 Temple Place - Suite 330                                          |
  // | Boston, MA 02111-1307, USA.                                          |
  // +----------------------------------------------------------------------+
  // +----------------------------------------------------------------------+
  // | Originado do Projeto BBBoletoFree que tiveram colaborações de Daniel |
  // | William Schultz e Leandro Maniezo que por sua vez foi derivado do	  |
  // | PHPBoleto de João Prado Maia e Pablo Martins F. Costa				        |
  // |                                  																	  |
  // | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
  // | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
  // +----------------------------------------------------------------------+
  */
?>

  <!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Transitional//EN'>
  <HTML>
  <HEAD>
  <TITLE>{{identificacao}}</TITLE>
  <META http-equiv=Content-Type content=text/html charset=utf-8>
  <style type=text/css>
  <!--.cp {  font: bold 10px Arial; color: black}
  <!--.ti {  font: 9px Arial, Helvetica, sans-serif}
  <!--.ld { font: bold 15px Arial; color: #000000}
  <!--.ct { font: 9px "Arial Narrow"; color: #000033}
  <!--.cn { font: 9px Arial; color: black }
  <!--.bc { font: bold 20px Arial; color: #000000 }
  <!--.ld2 { font: bold 12px Arial; color: #000000 }
  <!--.logo_empresa { width: 80px; }

  .bold { font-weight: bold; }
  .vencimento2 {background-color: #ccc;}

  </style> 
  </head>

  <BODY text=#000000 bgColor=#ffffff topMargin=0 rightMargin=0>
    <table width=666 cellspacing=0 cellpadding=0 border=0>
      <tr>
        <td valign=top class=cp>
          <DIV ALIGN="CENTER">Instruções
            de Impress&atilde;o</DIV>
        </TD>
      </TR>
      <TR>
        <TD valign=top class=cp>
          <DIV ALIGN="left">
            <p>
              <li>Imprima em impressora jato de tinta (ink jet) ou laser em qualidade normal ou alta (N&atilde;o use modo
                econ&ocirc;mico).<br>
              <li>Utilize folha A4 (210 x 297 mm) ou Carta (216 x 279 mm) e margens m&iacute;nimas &agrave; esquerda e
                &agrave; direita do formul&aacute;rio.<br>
              <li>Corte na linha indicada. N&atilde;o rasure, risque, fure ou dobre a regi&atilde;o onde se encontra o
                c&oacute;digo de barras.<br>
              <li>Caso n&atilde;o apare&ccedil;a o c&oacute;digo de barras no final, clique em F5 para atualizar esta
                tela.
              <li>Caso tenha problemas ao imprimir, copie a seq&uuml;encia num&eacute;rica abaixo e pague no caixa
                eletr&ocirc;nico ou no internet banking:<br><br>
                <span class="ld2">
                  &nbsp;&nbsp;&nbsp;&nbsp;Linha Digit&aacute;vel: &nbsp;{{linha_digitavel}}
                  &nbsp;&nbsp;&nbsp;&nbsp;Valor: &nbsp;&nbsp;R$ {{valor_boleto}}
                </span>
          </DIV>
        </td>
      </tr>
    </table><br>
    <table cellspacing=0 cellpadding=0 width=666 border=0>
      <TBODY>
        <TR>
          <TD class=ct width=666><img height=1 src=imagens/6.png width=665 border=0></TD>
        </TR>
        <TR>
          <TD class=ct width=666>
            <div align=right><b class=cp>Recibo
                do Sacado</b></div>
          </TD>
        </tr>
      </tbody>
    </table>
    <table width=666 cellspacing=5 cellpadding=0 border=0>
      <tr>
        <td width=41></TD>
      </tr>
    </table>
    <BR>
    <table cellspacing=0 cellpadding=0 width=666 border=0>
      <tr>
        <td class=cp width=150>
          <span class="campo"><IMG src="imagens/logosicoob.png" width="150" height="40" border=0></span></td>
        <td width=3 valign=bottom><img height=22 src=imagens/3.png width=2 border=0></td>
        <td class=cpt width=58 valign=bottom>
          <div align=center>
            <font class=bc>{{codigo_banco_com_dv}}</font>
          </div>
        </td>
        <td width=3 valign=bottom><img height=22 src=imagens/3.png width=2 border=0></td>
        <td class=ld align=right width=453 valign=bottom><span class=ld>
            <span class="campotitulo">
              {{linha_digitavel}}</span></span></td>
      </tr>
      <tbody>
        <tr>
          <td colspan=5><img height=2 src=imagens/2.png width=666 border=0></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 border=0>
      <tbody>
        <tr>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=298 height=13>Nome do benefici&aacute;rio/CPF/CNPJ</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=126 height=13>Ag&ecirc;ncia/C&oacute;digo
            do Cedente</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=34 height=13>Esp&eacute;cie</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=53 height=13>Quantidade</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=120 height=13>Nosso
            n&uacute;mero</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
            
        </tr>
        <tr>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=298 height=12>
            <span class="campo">{{cedente}}</span></td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=126 height=12>
            <span class="campo">
              {{agencia_codigo}} </span></td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=34 height=12><span class="campo">
              {{especie}}</span>
          </td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=53 height=12><span class="campo">
              {{quantidade}}</span>
          </td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top align=right width=120 height=12>
            <span class="campo"> {{nosso_numero}} &nbsp;&nbsp; </span>
          </td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
        </tr>
        <tr>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=298 height=1><img height=1 src=imagens/2.png width=298 border=0></td>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=126 height=1><img height=1 src=imagens/2.png width=126 border=0></td>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=34 height=1><img height=1 src=imagens/2.png width=34 border=0></td>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=53 height=1><img height=1 src=imagens/2.png width=53 border=0></td>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=120 height=1><img height=1 src=imagens/2.png width=120 border=0></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 border=0>
      <tbody>
        <tr>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top colspan=3 height=13>N&uacute;mero
            do documento</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=132 height=13>CPF/CNPJ</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=134 height=13 >Vencimento</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=180 height=13>Valor
            documento</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
        </tr>
        <tr>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top colspan=3 height=12>
            <span class="campo">
              {{numero_documento}} &nbsp;&nbsp;</span></td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=132 height=12>
            <span class="campo">
              {{cpf_cnpj}} </span></td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=134 height=12>
            <span class="campo">
              {{data_vencimento}}&nbsp;&nbsp; </span></td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top align=right width=180 height=12>
            <span class="campo">
              {{valor_boleto}}&nbsp;&nbsp; </span></td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
        </tr>
        <tr>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=113 height=1><img height=1 src=imagens/2.png width=113 border=0></td>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=72 height=1><img height=1 src=imagens/2.png width=72 border=0></td>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=132 height=1><img height=1 src=imagens/2.png width=132 border=0></td>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=134 height=1><img height=1 src=imagens/2.png width=134 border=0></td>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=180 height=1><img height=1 src=imagens/2.png width=180 border=0></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 border=0>
      <tbody>
        <tr>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=113 height=13>(-)
            Desconto / Abatimentos</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=112 height=13>(-)
            Outras deduções</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=113 height=13>(+)
            Mora / Multa</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=113 height=13>(+)
            Outros acr&eacute;scimos</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=180 height=13>(=) Valor cobrado</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
        </tr>
        <tr>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top align=right width=113 height=12></td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top align=right width=112 height=12></td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top align=right width=113 height=12></td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top align=right width=113 height=12></td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top align=right width=180 height=12></td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
        </tr>
        <tr>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=113 height=1><img height=1 src=imagens/2.png width=113 border=0></td>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=112 height=1><img height=1 src=imagens/2.png width=112 border=0></td>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=113 height=1><img height=1 src=imagens/2.png width=113 border=0></td>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=113 height=1><img height=1 src=imagens/2.png width=113 border=0></td>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=180 height=1><img height=1 src=imagens/2.png width=180 border=0></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 border=0>
      <tbody>
        <tr>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=659 height=13>Pagador</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
        </tr>
        <tr>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=659 height=12>
            <span class="campo">
              {{sacado}} </span></td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
        </tr>
        <tr>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=659 height=1><img height=1 src=imagens/1.png width=659 border=0></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 border=0>
      <tbody>
        <tr>
          <td class=ct width=7 height=12></td>
          <td class=ct width=564>Demonstrativo</td>
          <td class=ct width=7 height=12></td>
          <td class=ct width=88>Autentica&ccedil;&atilde;o
            mec&acirc;nica</td>
        </tr>
        <tr>
          <td width=7></td>
          <td class=cp width=564>
            <span class="campo">
              {{demonstrativo1}}<br>
              {{demonstrativo2}}<br>
              {{demonstrativo3}}<br>
            </span>
          </td>
          <td width=7></td>
          <td width=88></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 width=666 border=0>
      <tbody>
        <tr>
          <td width=7></td>
          <td width=500 class=cp>
            <br><br><br>
          </td>
          <td width=159></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 width=666 border=0>
      <tr>
        <td class=ct width=666></td>
      </tr>
      <tbody>
        <tr>
          <td class=ct width=666>
            <div align=right>Corte na linha pontilhada</div>
          </td>
        </tr>
        <tr>
          <td class=ct width=666><img height=1 src=imagens/6.png width=665 border=0></td>
        </tr>
      </tbody>
    </table><br>
    <table cellspacing=0 cellpadding=0 width=666 border=0>
      <tr>
        <td class=cp width=150>
          <span class="campo"><IMG src="imagens/logosicoob.png" width="150" height="40" border=0></span></td>
        <td width=3 valign=bottom><img height=22 src=imagens/3.png width=2 border=0></td>
        <td class=cpt width=58 valign=bottom>
          <div align=center>
            <font class=bc>{{codigo_banco_com_dv}}</font>
          </div>
        </td>
        <td width=3 valign=bottom><img height=22 src=imagens/3.png width=2 border=0></td>
        <td class=ld align=right width=453 valign=bottom><span class=ld>
            <span class="campotitulo">
              {{linha_digitavel}}</span></span></td>
      </tr>
      <tbody>
        <tr>
          <td colspan=5><img height=2 src=imagens/2.png width=666 border=0></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 border=0>
      <tbody>
        <tr>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=472 height=13>Local de pagamento</td>
          <td class="ct vencimento2" valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class="ct vencimento2" valign=top width=180 height=13>Vencimento</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
        </tr>
        <tr>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=472 height=12 class="bold">PAGAVEL PREFERENCIALMENTE NO SICOOB</td>
          <td class="cp vencimento2" valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class="cp vencimento2"  valign=top align=right width=180 height=12>
            <span class="campo bold">
              {{data_vencimento}} &nbsp;&nbsp;</span></td>
          <td class=cp valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
        </tr>
        <tr>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=472 height=1><img height=1 src=imagens/2.png width=472 border=0></td>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=180 height=1><img height=1 src=imagens/2.png width=180 border=0></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 border=0>
      <tbody>
        <tr>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=472 height=13>Nome do benefici&aacute;rio/CPF/CNPJ/Endere&ccedil;o</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/2.png width=1 border=0></td>
          <td class=ct valign=top width=180 height=13>Coop. contratante/Co&oacute;digo do Benef</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
        </tr>
        <tr>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=472 height=12>
            <span class="campo">
              {{cedente}} </span></td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top align=right width=180 height=12>
            <span class="campo">
              {{agencia_codigo}}&nbsp;&nbsp;</span></td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
        </tr>
        <tr>
          <td valign=top width=7 height=1><img height=1 src=imagens/1.png width=7 border=0></td>
          <td valign=top width=472 height=1><img height=1 src=imagens/1.png width=472 border=0></td>
          <td valign=top width=7 height=1><img height=1 src=imagens/1.png width=7 border=0></td>
          <td valign=top width=180 height=1><img height=1 src=imagens/1.png width=180 border=0></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 border=0>
      <tbody>
        <tr>
          <td class=ct valign=top width=7 height=13>
            <img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=113 height=13>Data do documento</td>
          <td class=ct valign=top width=7 height=13> <img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=140 height=13>N° documento</td>
          <td class=ct valign=top width=7 height=13> <img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=62 height=13>Esp&eacute;cie doc.</td>
          <td class=ct valign=top width=7 height=13> <img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=34 height=13>Aceite</td>
          <td class=ct valign=top width=7 height=13> <img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=95 height=13>Data processamento</td>
          <td class=ct valign=top width=7 height=13> <img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=180 height=13>Nosso n&uacute;mero</td>
          <td class=ct valign=top width=7 height=13> <img height=13 src=imagens/1.png width=1 border=0></td>
        </tr>
        <tr>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=113 height=12>
            <div align=left>
              <span class="campo">
                {{data_documento}} </span></div>
          </td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=140 height=12>
            <span class="campo">
              {{numero_documento}} </span></td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=62 height=12>
            <div align=left><span class="campo">
                {{especie_doc}} </span>
            </div>
          </td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=34 height=12>
            <div align=left><span class="campo">
                {{aceite}}</span>
            </div>
          </td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=95 height=12>
            <div align=left>
              <span class="campo"> {{data_processamento}} </span>
            </div>
          </td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top align=right width=180 height=12>
            <span class="campo"> {{nosso_numero}} &nbsp;&nbsp; </span>
          </td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>

        </tr>
        <tr>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=113 height=1><img height=1 src=imagens/2.png width=113 border=0></td>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=140 height=1><img height=1 src=imagens/2.png width=140 border=0></td>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=62 height=1><img height=1 src=imagens/2.png width=62 border=0></td>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=34 height=1><img height=1 src=imagens/2.png width=34 border=0></td>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=95 height=1><img height=1 src=imagens/2.png width=95 border=0></td>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=180 height=1>
            <img height=1 src=imagens/2.png width=180 border=0></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 border=0>
      <tbody>
        <tr>
          <td class=ct valign=top width=7 height=13> <img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top COLSPAN="3" height=13>Uso
            do banco</td>
          <td class=ct valign=top height=13 width=7> <img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=83 height=13>Carteira</td>
          <td class=ct valign=top height=13 width=7>
            <img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=53 height=13>Esp&eacute;cie</td>
          <td class=ct valign=top height=13 width=7>
            <img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=123 height=13>Quantidade</td>
          <td class=ct valign=top height=13 width=7>
            <img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=72 height=13> Valor</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=180 height=13>(=) Valor documento</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
        </tr>
        <tr>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td valign=top class=cp height=12 COLSPAN="3">
            <div align=left>
            </div>
          </td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=83> 
            <div align=left> <span class="campo">
                {{carteira}}</span></div>
          </td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=53>
            <div align=left><span class="campo">
                {{especie}}</span>
            </div>
          </td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=123><span class="campo">
              {{quantidade}}</span>
          </td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=72>
            <span class="campo">
              {{valor_unitario}} </span></td>
          <td class=cp valign=top width=7 height=12> <img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top align=right width=180 height=12>
            <span class="campo">
              {{valor_boleto}} &nbsp;&nbsp; </span></td>
          <td class=cp valign=top width=7 height=12> <img height=12 src=imagens/1.png width=1 border=0></td>
        </tr>
        <tr>
          <td valign=top width=7 height=1> <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=75 border=0></td>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=31 height=1><img height=1 src=imagens/2.png width=31 border=0></td>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=83 height=1><img height=1 src=imagens/2.png width=83 border=0></td>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=53 height=1><img height=1 src=imagens/2.png width=53 border=0></td>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=123 height=1><img height=1 src=imagens/2.png width=123 border=0></td>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=72 height=1><img height=1 src=imagens/2.png width=72 border=0></td>
          <td valign=top width=7 height=1>
            <img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=180 height=1><img height=1 src=imagens/2.png width=180 border=0></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 width=667 border=0>
      <tbody>
        <tr>
          <td align=right width=10>
            <table cellspacing=0 cellpadding=0 border=0 align=left>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
                </tr>
                <tr>
                  <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=1 border=0></td>
                </tr>
              </tbody>
            </table>
          </td>
          <td valign=top width=480 rowspan=5>
            <span class=ct>Informações de responsabilidade do benefici&aacute;rio
            </span><br><br><span class=cp>
              <span class=campo>
                {{instrucoes1}}<br>
                {{instrucoes2}}<br>
                {{instrucoes3}}<br>
                {{instrucoes4}}</span><br><br>
            </span>
          </td>
          <td align=right width=180>
            <table cellspacing=0 cellpadding=0 border=0>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
                  <td class=ct valign=top width=180 height=13>(-)
                    Desconto / Abatimentos</td>
                  <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
                  <td class=cp valign=top align=right width=180 height=12></td>
                  <td class=cp valign=top width=7 height=12> <img height=12 src=imagens/1.png width=1 border=0></td>
                </tr>
                <tr>
                  <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
                  <td valign=top width=180 height=1><img height=1 src=imagens/2.png width=180 border=0></td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
        <tr>
          <td align=right width=10>
            <table cellspacing=0 cellpadding=0 border=0 align=left>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
                </tr>
                <tr>
                  <td valign=top width=7 height=1>
                    <img height=1 src=imagens/2.png width=1 border=0></td>
                </tr>
              </tbody>
            </table>
          </td>
          <td align=right width=180>
            <table cellspacing=0 cellpadding=0 border=0>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
                  <td class=ct valign=top width=180 height=13>(-) Outras deduções</td>
                  <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12> <img height=12 src=imagens/1.png width=1 border=0></td>
                  <td class=cp valign=top align=right width=180 height=12></td>
                  <td class=cp valign=top width=7 height=12> <img height=12 src=imagens/1.png width=1 border=0></td>
                </tr>
                <tr>
                  <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
                  <td valign=top width=180 height=1><img height=1 src=imagens/2.png width=180 border=0></td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
        <tr>
          <td align=right width=10>
            <table cellspacing=0 cellpadding=0 border=0 align=left>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13>
                    <img height=13 src=imagens/1.png width=1 border=0></td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
                </tr>
                <tr>
                  <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=1 border=0></td>
                </tr>
              </tbody>
            </table>
          </td>
          <td align=right width=170>
            <table cellspacing=0 cellpadding=0 border=0>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
                  <td class=ct valign=top width=180 height=13>(+) Mora / Multa</td>
                  <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
                  <td class=cp valign=top align=right width=180 height=12></td>
                  <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
                </tr>
                <tr>
                  <td valign=top width=7 height=1> <img height=1 src=imagens/2.png width=7 border=0></td>
                  <td valign=top width=180 height=1>
                    <img height=1 src=imagens/2.png width=180 border=0></td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
        <tr>
          <td align=right width=10>
            <table cellspacing=0 cellpadding=0 border=0 align=left>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
                </tr>
                <tr>
                  <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=1 border=0></td>
                </tr>
              </tbody>
            </table>
          </td>
          <td align=right width=170>
            <table cellspacing=0 cellpadding=0 border=0>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
                  <td class=ct valign=top width=180 height=13>(+)
                    Outros acr&eacute;scimos</td>
                  <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
                  <td class=cp valign=top align=right width=180 height=12></td>
                  <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
                </tr>
                <tr>
                  <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
                  <td valign=top width=180 height=1><img height=1 src=imagens/2.png width=180 border=0></td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
        <tr>
          <td align=right width=10>
            <table cellspacing=0 cellpadding=0 border=0 align=left>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
                </tr>
                <tr>
                  <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
                </tr>
              </tbody>
            </table>
          </td>
          <td align=right width=170>
            <table cellspacing=0 cellpadding=0 border=0>
              <tbody>
                <tr>
                  <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
                  <td class=ct valign=top width=180 height=13>(=) Valor cobrado</td>
                  <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
                </tr>
                  <td class=cp valign=top width=7 height=12><img height=13 src=imagens/1.png width=1 border=0></td>
                  <td class=cp valign=top align=right width=180 height=13></td>
                  <td class=cp valign=top width=7 height=12><img height=13 src=imagens/1.png width=1 border=0></td>
                </tr>
                <tr>
                  <td valign=top width=7 height=0> <img height=0 src=imagens/2.png width=7 border=0></td>
                  <td valign=top width=180 height=0>
                    <img height=0 src=imagens/2.png width=180 border=0></td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 width=666 border=0>
      <tbody>
        <tr>
          <td valign=top width=666 height=1><img height=1 src=imagens/2.png width=666 border=0></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 border=0>
      <tbody>
        <tr>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=ct valign=top width=659 height=13>Pagador</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
        </tr>
        <tr>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=659 height=12><span class="campo">
              {{sacado}}</span>
          </td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 border=0>
      <tbody>
        <tr>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=659 height=12><span class="campo">
              {{endereco1}}</span>
          </td>
          <td class=cp valign=top width=7 height=12><img height=12 src=imagens/1.png width=1 border=0></td>
        </tr>
      </tbody>
    </table>
    <table cellspacing=0 cellpadding=0 border=0>
      <tbody>
        <tr>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
          <td class=cp valign=top width=472 height=13><span class="campo">{{endereco2}}</span></td>
          <td class=ct valign=top width=7 height=13></td>
          <td class=ct valign=top width=180 height=13>C&oacute;d. baixa</td>
          <td class=ct valign=top width=7 height=13><img height=13 src=imagens/1.png width=1 border=0></td>
        </tr>
        <tr>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=472 height=1><img height=1 src=imagens/2.png width=472 border=0></td>
          <td valign=top width=7 height=1><img height=1 src=imagens/2.png width=7 border=0></td>
          <td valign=top width=180 height=1><img height=1 src=imagens/2.png width=180 border=0></td>
        </tr>
      </tbody>
    </table>
    <TABLE cellSpacing=0 cellPadding=0 border=0 width=666>
      <TBODY>
        <TR>
          <TD class=ct width=7 height=12></TD>
          <TD class=ct width=409>Sacador/Avalista</TD>
          <TD class=ct width=250>
            <div align=right>Autentica&ccedil;&atilde;o
              mec&acirc;nica - <b class=cp>Ficha de Compensa&ccedil;&atilde;o</b></div>
          </TD>
        </TR>
        <TR>
          <TD class=ct colspan=3></TD>
        </tr>
      </tbody>
    </table>
    <TABLE cellSpacing=0 cellPadding=0 width=666 border=0>
      <TBODY>
        <TR>
          <TD vAlign=bottom align=left height=50>{{codigo_barras}}
          </TD>
        </tr>
      </tbody>
    </table>
    <TABLE cellSpacing=0 cellPadding=0 width=666 border=0>
      <TR>
        <TD class=ct width=666></TD>
      </TR>
      <TBODY>
        <TR>
          <TD class=ct width=666>
            <div align=right>Corte
              na linha pontilhada</div>
          </TD>
        </TR>
        <TR>
          <TD class=ct width=666><img height=1 src=imagens/6.png width=665 border=0></TD>
        </tr>
      </tbody>
    </table>
  </BODY>

  </HTML>