<?php
echo $this->Form->create('User', array('action' => 'login'));
echo $this->Form->input('openid', array('label' => 'OpenID:'));
echo $this->Form->end('Login');
?>
