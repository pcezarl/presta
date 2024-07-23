<?php
session_start();
unset($_SESSION["tri"]);
unset($_SESSION["ent"]);
unset($_SESSION["sem"]);
unset($_SESSION["valor_total"]);

session_destroy();
?>
<script>
self.close();
</script>

