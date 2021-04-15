<?php

class UserController extends Controller 
{
	/**
	* check if password and confirmation ok
	* @param	string $pw	password
	* @param	string $confirm	password confirmation 
	* @return	string	errormessage/empty if ok
	*/
	private function check_password($pw, $confirm)
	{
		$this->f3->set('LANGUAGE',$this->f3->get('SESSION.lang'));
		if($confirm===""){
			return $this->f3->get('i18n_password_confirmation_empty');
		}
		else if(strlen($pw) < intval($this->f3->get('min_pw_length'))){
			return $this->f3->get('i18n_password_too_short', $this->f3->get('min_pw_length'));
		}
		else if($pw !== $confirm){
			return $this->f3->get('i18n_password_confirmation_wrong');
		}
		else {
			return "";
		}		
	}
	
	/**
	* confirmation for account creation from email 
	* check hash from mail
	* GET /confirm_registration?h=hash
	*/
	public function confirm_registration()
	{
		$hash=$_GET['h'];
		$user = new User($this->db,$this->f3->get("table_prefix"));
		$user->getByHash($hash);
		if($user->dry()){
			$this->f3->set('message',$this->f3->get('i18n_reg_conf_failed') );
			$this->f3->set('page_head',$this->f3->get('i18n_registration'));
		}
		elseif($user->activated==1)
		{
			$this->f3->set('message',$this->f3->get("i18n_reg_again") );
			$this->f3->set('page_head',$this->f3->get('i18n_registration'));
		}
		else 
		{
			$user->activate();
			$this->f3->set('page_head',$this->f3->get("i18n_registration"));
			$this->f3->set('message',$this->f3->get("i18n_reg_conf_success"));
		}
		$this->f3->set('view','page/message.htm');
	}
	
	/**
	* password reset requested
	* clicked on url with hash in mail 
	* pw_reset?h=hash
	*/
	public function pw_reset()
	{
		if($this->f3->get('SESSION.logged_in')){
			$this->f3->reroute('/');
		}
		if(null===$this->f3->get('GET.h')){
			$this->f3->error(403);
		}
		$user = new User($this->db,$this->f3->get("table_prefix"));
		$user->getByHash($_GET['h']);
		$this->f3->set('SESSION.user_id',$user->id);

		if($this->f3->VERB==="POST")
		{
			$pwcheck = $this->check_password( $this->f3->get('POST.new_password') , $this->f3->get('POST.confirm'));
			if (strlen($pwcheck) > 0) //pwcheck error message returned
			{
				$this->f3->set('message', $pwcheck);
				$this->f3->set('view','user/change-pw.htm');
			}
			else
			{
				$this->setpw($this->f3->get('POST.new_password'), $user->id);
				die;
			}
		}
		else {
			$this->f3->set('view','user/change-pw.htm');
		}
	}
	
	/**
	* change user password
	*/
	private function setpw( $newpw, $user_id )
	{
		$user = new User($this->db,$this->f3->get("table_prefix"));
		$user->getById($user_id);
		
		$password = password_hash($newpw, PASSWORD_BCRYPT);
		
		//check if user id = session id
		if(intval($user_id) === intval($this->f3->get('SESSION.user_id')))
		{
			$this->f3->set('POST.password', $password);
			$this->f3->set('POST.hash', '');
			$user->edit($user_id, $this->f3->get('POST'));
			$this->f3->clear('SESSION');
			$this->f3->set('SESSION.info_msg',$this->f3->get('i18n_password_changed'). ' '.$this->f3->get('i18n_password_pleaselogin') );
			$this->f3->reroute($this->f3->get('loginpage'));
		}
		else { 
			$this->f3->error(403);
		}
	}

	
	/**
	* /account
	* change password
	*/
	public function edit_registration()
	{
		if(!$this->f3->get('SESSION.logged_in')){
			$this->f3->reroute('/login');
		}
		if($this->f3->VERB==="POST"){
			$user = new User($this->db,$this->f3->get("table_prefix"));
			if(strlen(trim($this->f3->get('POST.password')))>0)
			{
				$pw_response=$this->check_password($this->f3->get('POST.password'),$this->f3->get('POST.pw_confirmation'));
				if($pw_response!=="")
				{
					$this->f3->set('SESSION.message_type',"error" );
					$this->f3->set('SESSION.message',$pw_response );
					$this->f3->set('POST.password',$this->f3->get('SESSION.password'));
				}
				else 
				{
					$this->f3->set('SESSION.message_type',"success" );
					$this->f3->set('SESSION.message',$this->f3->get('i18n_password_changed') );
					$password = password_hash($this->f3->get('POST.password'), PASSWORD_BCRYPT);
					$this->f3->set('POST.password', $password);
				}
				$user->edit($this->f3->get('SESSION.id'), $this->f3->POST);
				$this->f3->set('SESSION.logged_in', 1);
				$user->login($user->id);
				$this->f3->reroute('account');
			}
			else
			{
				$this->f3->set('SESSION.message_type',"error" );
				$this->f3->set('SESSION.message',$pw_response );
			}
		}
		if(null!==$this->f3->get('SESSION.message'))
		{
			$this->f3->set('message',$this->f3->get('SESSION.message'));
			if(null===$this->f3->get('SESSION.message_type')){
				$this->f3->set('message_type',"error");
			}
			else{
				$this->f3->set('message_type',$this->f3->get('SESSION.message_type'));
			}
			$this->f3->clear('SESSION.message_type');
			$this->f3->clear('SESSION.message');
		}
		$this->f3->set('view','user/editregistration.htm');
	}
	
	/**
	* send the actual mail to activate the user account
	*/
	public function sendactmail($email, $hash)
	{
		$confirmation_link = $this->f3->SCHEME."://".$this->f3->HOST ."/confirm_registration?h=".$hash;
		$mail = new Mail();
		return $mail->send( // sender, recipient, subject, msg
			$this->f3->get('from_email') , 
			$email, 
			$this->f3->get('i18n_confirmation_mail_subject') . " " . $this->f3->get('site'),
			$this->f3->get('i18n_confirmation_mail_message')."<a href=\"".$confirmation_link."\">".$confirmation_link . "</a>"
		);
	}
	
	/**
	* send the actual password reset mail
	*/
	private function pw_reset_mail($email, $hash)
	{
		$confirmation_link = $this->f3->SCHEME."://".$this->f3->HOST ."/pw_reset?h=".$hash;
		$mail = new Mail();
		$mail->send( // sender, recipient, subject, msg
			$this->f3->get('from_email') , 
			$email, 
			"Your account on " . $this->f3->get('site'),
			$this->f3->get('i18n_reset_pw_mail_message')."<a href=\"".$confirmation_link."\">".$confirmation_link . "</a>"
		);
	}
	
	/**
	* page /sendactivationmail
	* request new activation mail
	* account created, but not created.
	* link appears in error message when trying to log in 
	* but not yet registered
	*/
	public function sendactivationmail()
	{
		if($this->f3->exists('POST.sendmail'))
		{
			$hash=$this->createHash();
			$user = new User($this->db,$this->f3->get("table_prefix"));
			$user->getByEmail($this->f3->get('POST.email'));
			$this->f3->set('POST.hash', $hash);
			$user->edit($user->id, $this->f3->get("POST"));
			if($this->sendactmail($this->f3->get('POST.email'), $hash))
			{
				$this->f3->set('page_head',$this->f3->get('i18n_registration'));
				$this->f3->set('message', $this->f3->get('i18n_conf_mail_sent'));
			}
			else
			{
				$this->f3->set('page_head',$this->f3->get('i18n_error_header'));
				$this->f3->set('message', $this->f3->get('i18n_other_error'));	
			}
			$this->f3->set('view','page/message.htm');
		}
		else {
			$this->f3->set('view','user/send_activation_mail.htm');
		}
	}
	
	/**
	* page /register
	* create new user
	*/
	public function create()
	{
		if($this->f3->exists('POST.create'))
		{
			$pwcheck = $this->check_password( $this->f3->get('POST.password'), $this->f3->get('POST.confirm'));
			if (strlen($pwcheck) > 0)
			{ 
				$this->f3->set('message', $pwcheck);
				$this->f3->set('view','user/create.htm');
			}
			else
			{
				$password = password_hash($this->f3->get('POST.password'), PASSWORD_BCRYPT);
				$this->f3->set('POST.password', $password);
				$hash = $this->createHash();
				$this->f3->set('POST.hash', $hash);
				$this->f3->set('POST.user_type',1);
				$this->f3->set('POST.activated',0);
				$user = new User($this->db,$this->f3->get("table_prefix"));
				$user_added=$user->add($this->f3->get("POST"));
				
				if($user_added==1)
				{
					if($this->sendactmail($this->f3->get('POST.email'), $hash))
					{
						$this->f3->set('page_head',$this->f3->get('i18n_registration'));
						$this->f3->set('message', $this->f3->get('i18n_conf_mail_sent'));
					}
					else
					{
						$this->f3->set('page_head',$this->f3->get('i18n_error_header'));
						$this->f3->set('message', $this->f3->get('i18n_other_error'));	
					}

					$this->f3->set('view','page/message.htm');
				}
				else if($user_added==10) //user taken
				{
					$this->f3->set('message', $this->f3->get('i18n_login_taken'));
					$this->f3->set('view','user/create.htm');
				}
				else if($user_added==11) //email taken
				{
					if($user->activated==0) {
						$this->f3->set('message', $this->f3->get('i18n_not_activated'));	
					}
					else {
						$this->f3->set('message', $this->f3->get('i18n_email_taken'));						
					}
					$this->f3->set('view','user/create.htm');
				}
			}
		}
		else {
			$this->f3->set('view','user/create.htm');
		}
	}

	/**
	* user login
	*/
	public function login()
	{
		if( $this->f3->get('SESSION.logged_in')) {
			if($this->f3->get('SESSION.user_type')==100){
				$this->f3->reroute('/'.$this->f3->get('adminpage'));	
			}
			else{
				$this->f3->reroute('/');
			}
		}
		else if($this->f3->VERB=='POST')
		{
			$user_id="not logged in";

			$user = new User($this->db,$this->f3->get("table_prefix"));
			$user->getByName( $this->f3->get('POST.username') );

			$loginsuccess=false;
			if($user->dry())
			{				
				$this->f3->logger->write( "LOG IN: ".$this->f3->get('POST.username')." (".$user->id.") login failed - unknown user", 'r');
				sleep(2);
				$this->f3->set('message', $this->f3->get('i18n_wrong_login'));
			} 
			elseif(! password_verify($this->f3->get('POST.password'), $user->password))
			{
				$this->f3->logger->write( "LOG IN: ".$this->f3->get('POST.username')." (".$user->id.") login failed " . password_verify($this->f3->get('POST.password'), $user->password), 'r');
				sleep(2);
				$this->f3->set('message', $this->f3->get('i18n_wrong_login'));
			}
			else if ($user->activated===0)
			{
				$this->f3->logger->write( "LOG IN: ".$this->f3->get('POST.username')." not activated ", 'r');
				$this->f3->set('message',  $this->f3->get('i18n_not_activated'));
			}
			else {
				$loginsuccess=true;
			}
			if(!$loginsuccess)
			{
				$this->f3->set('page_head','Login');
				$this->f3->set('view','user/login.htm');
			}
			else
			{
				$this->f3->set('SESSION.user_id', $user->id);
				$user->login($user->id);
				$this->f3->logger->write( "LOG IN: ".$this->f3->get('POST.username')." login success (ip: " .$ip .")", 'r');
				$this->f3->set('SESSION.logged_in', 'true');
				$this->f3->set('SESSION.timestamp', time());
				if($this->f3->get('SESSION.user_type')==100){
					$this->f3->reroute('/'.$this->f3->get('adminpage'));
				}
				else {
					$this->f3->reroute('/');
				}
			}
		} 
		else
		{
			if(null!==$this->f3->get('SESSION.info_msg')){
				$this->f3->set('info_msg',$this->f3->get('SESSION.info_msg'));
				$this->f3->clear('SESSION.info_msg');
			}
			$this->f3->set('page_head','Login');
			$this->f3->set('view','user/login.htm');
		}

	}
	
	/**
	* /logout
	* clear session
	*/
	public function logout()
	{
		$this->f3->clear('SESSION');
		$this->f3->set('page_head','Logout');
		$this->f3->reroute('/');
	}

	/**
	* request new password, ask mail->send hash
	*/
	public function lostpassword()
	{
		if($this->f3->exists('POST.reset_pw'))
		{
			$user = new User($this->db,$this->f3->get("table_prefix"));
			$user->getByEmail($this->f3->get('POST.email'));
			if(! $user->dry())
			{
				$hash = $this->createHash();
				$user->setHash($hash);
				$this->pw_reset_mail($this->f3->get('POST.email'), $hash);
			}
			$this->f3->set('page_head', $this->f3->get('i18n_new_pw_header'));
			$this->f3->set('message', $this->f3->get('i18n_lostpw_request_msg'));
			$this->f3->set('view','page/message.htm');
		}
		else {
			$this->f3->set('view','user/reset-pw.htm');
		}
	}

	private function createHash() {
		return md5( time(). $this->f3->get('POST.username') . $this->f3->get('POST.email') );
	}

}