<?php 

class UsersController extends AppController {
    public $components = array('Openid' => array('use_database' => false, 'accept_google_apps' => false));
    public $uses = array();
    
    public function login() { 
        header('X-XRDS-Location: http://' . $_SERVER['SERVER_NAME'] . $this->webroot . 'users/xrds');

        $returnTo = 'http://'.$_SERVER['SERVER_NAME'].$this->webroot;

        if ($this->request->isPost() && !$this->Openid->isOpenIDResponse()) {
            $this->makeOpenIDRequest($this->data['User']['openid'], $returnTo);
        }

        if ($this->Openid->isOpenIDResponse()) {
            $this->handleOpenIDResponse($returnTo);
        }
    }

    public function xrds() {
        $this->layout = 'xml/default';
        $this->response->header('Content-type: application/xrds+xml');
        $this->set('returnTo', Router::url($this->webroot, true));
    }

    private function makeOpenIDRequest($openid, $returnTo) {
        try {
            // used by Google, Yahoo
            $axSchema = 'axschema.org';
            $attributes[] = Auth_OpenID_AX_AttrInfo::make('http://'.$axSchema.'/namePerson', 1, true, 'ax_fullname');
            $attributes[] = Auth_OpenID_AX_AttrInfo::make('http://'.$axSchema.'/contact/email', 1, true, 'ax_email');

            // used by MyOpenID (Google supports this schema for /contact/email only)
            $openidSchema = 'schema.openid.net';
            $attributes[] = Auth_OpenID_AX_AttrInfo::make('http://'.$openidSchema.'/namePerson', 1, true, 'fullname');
            $attributes[] = Auth_OpenID_AX_AttrInfo::make('http://'.$openidSchema.'/contact/email', 1, true, 'email');

            $this->Openid->authenticate($openid, $returnTo, 'http://'.$_SERVER['SERVER_NAME'], array('ax' => $attributes, 
                                                                                                     'sreg_required' => array('email', 'fullname'), 
                                                                                                     'sreg_optional' => array('nickname', 'gender')));
        } catch (Exception $e) {
            $this->debug($e);
        }
    }

    private function handleOpenIDResponse($returnTo) {
        $response = $this->Openid->getResponse($returnTo);

        if ($response->status == Auth_OpenID_CANCEL) {
            echo 'Verification cancelled';
        } elseif ($response->status == Auth_OpenID_FAILURE) {
            echo 'OpenID verification failed: '.$response->message;
        } elseif ($response->status == Auth_OpenID_SUCCESS) {
            echo 'Successfully authenticated!<br />';

            $openid = $response->identity_url;
            $this->debug($openid);

            $sregResponse = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
            $sreg = $sregResponse->contents();
            $this->debug($sreg);

            $axResponse = Auth_OpenID_AX_FetchResponse::fromSuccessResponse($response);
            $this->debug($axResponse);
            if ($axResponse) {
                $this->debug($axResponse->get('http://axschema.org/namePerson'));
                $this->debug($axResponse->get('http://axschema.org/contact/email'));
                $this->debug($axResponse->get('http://schema.openid.net/namePerson'));
                $this->debug($axResponse->get('http://schema.openid.net/contact/email'));
            }
        }
        exit;
    }

    // slightly modified version of the debug function in cake/basics.php, shows debug output even if debug == 0
    private function debug($var = false, $showHtml = false, $showFrom = true) {
        if ($showFrom) {
            $calledFrom = debug_backtrace();
            echo '<strong>' . substr(str_replace(ROOT, '', $calledFrom[0]['file']), 1) . '</strong>';
            echo ' (line <strong>' . $calledFrom[0]['line'] . '</strong>)';
        }
        echo "\n<pre class=\"cake-debug\">\n";

        $var = print_r($var, true);
        if ($showHtml) {
            $var = str_replace('<', '&lt;', str_replace('>', '&gt;', $var));
        }
        echo $var . "\n</pre>\n";
    }
}
