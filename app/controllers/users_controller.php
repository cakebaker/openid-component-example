<?php 

class UsersController extends AppController {
    public $components = array('Openid' => array('use_database' => false, 'accept_google_apps' => false), 'RequestHandler');
    public $uses = array();
    
    public function login() { 
        $returnTo = 'http://'.$_SERVER['SERVER_NAME'].'/users/login';
		
        if ($this->RequestHandler->isPost()) {   
    	    $this->makeOpenIDRequest($this->data['User']['openid'], $returnTo);
        }
    	
        if ($this->Openid->isOpenIDResponse()) {
            $this->handleOpenIDResponse($returnTo);
        }
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
            debug($e);
        }
    }
    
    private function handleOpenIDResponse($returnTo) {
    	$response = $this->Openid->getResponse($returnTo);
	
	if ($response->status == Auth_OpenID_CANCEL) {
	    echo 'Verification cancelled';
	} elseif ($response->status == Auth_OpenID_FAILURE) {
	    echo 'OpenID verification failed: '.$response->message;
	} elseif ($response->status == Auth_OpenID_SUCCESS) {
	    echo 'Successfully authenticated!';

	    $openid = $response->identity_url;
	    debug($openid);

	    $sregResponse = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
	    $sreg = $sregResponse->contents();
	    debug($sreg);
	        
            $axResponse = Auth_OpenID_AX_FetchResponse::fromSuccessResponse($response);
            debug($axResponse);
            debug($axResponse->get('http://axschema.org/namePerson'));
            debug($axResponse->get('http://axschema.org/contact/email'));
            debug($axResponse->get('http://schema.openid.net/namePerson'));
            debug($axResponse->get('http://schema.openid.net/contact/email'));
	}
        exit;
    }
}
