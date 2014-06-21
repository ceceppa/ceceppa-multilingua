<?php header("Content-type: text/css", true); ?>
<?php
  $count = @intval( $_GET[ 'langs' ] );
?>

/* Bandiere in "Tutti gli articoli" */
#cml_flags, .cml_flags {
  width: <?php echo $count * 35 ?>px;
  text-align: center;
  white-space:nowrap;
  padding: 0;
  margin: 0;
}

.column-cml_flags a {
  border-bottom:2px solid transparent;
  display: inline-block;
  margin-right: 10px;
  padding: 2px;
  width: 15px;
  opacity: 0.4;
  transition: all 0.2s;
}
table tr:hover .column-cml_flags a {
  opacity: 0.7;
}

.column-cml_flags a:hover {
  opacity: 0.9 !important;
  border-bottom-color: #80B3FF;
}
.column-cml_flags a.cml-filter-current {
  border-bottom:2px solid #BF0000;
  text-align: center;
  opacity: 1;
}
