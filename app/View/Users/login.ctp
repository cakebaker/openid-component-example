<?php
echo $this->Form->create('User', array('action' => 'login'));
echo $this->Form->input('openid', array('label' => 'OpenID:'));
echo $this->Form->end('Login');
?>
<ul>
    <li>
    Source code available at <a href="https://github.com/cakebaker/openid-component-example/">https://github.com/cakebaker/openid-component-example/</a>
    </li>
</ul>
