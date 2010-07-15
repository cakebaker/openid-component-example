<?php
echo $form->create('User', array('action' => 'login'));
echo $form->input('openid', array('label' => 'OpenID:'));
echo $form->end('Login');
?>
