<?php

function smarty_modifier_seotitle($s)
{
  $c = array (' ','-','/','\\',',','.','#',':',';','\'','"','[',']','{',
      '}',')','(','|','`','~','!','@','%','$','^','&','*','=','?','+');

  $s = str_replace($c, '_', $s);

  $s = preg_replace(
        array('/-+/',
              '/-$/',
              '/-ytmsinternsignature/'),
        array('_',
              '',
              'ytmsinternsignature') ,
        $s);
  return $s;
}

?>
