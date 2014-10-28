<?php
namespace SlimPlugin\Account;
class Accounts extends \SSP\Mongo\Model\Basic\MongoModel
{
	public function __construct()
	{
		$this->setPath(__DIR__."/specs/");
		parent::__construct();
	}
	public function setPw($p)
	{
		$pwhash = hash("sha512", $p);
		$this->setPwhash($pwhash);
	}
	public function checkMailName($mail)
	{
		$res = Accounts::find($this->mongo, array("mail"=>$mail));
		if(count($res)>0)
		{
			return false;
		}
		return true;
		#var_dump($res);
	}
	public static function login($mail, $pw)
	{
		$pwhash = hash("sha512", $pw);
		$res = Accounts::find(NULL, array("mail"=>$mail, "pwhash"=>$pwhash));
		if(count($res)==1)
		{
			if($res[0]->getStatus()=="aktiv")
			{
				return array($res[0]->getId(), $res[0]->getStatus());
			}
			return array(false, $res[0]->getStatus());
		}
		else
		{
			return array(false, false);
		}
	}
	public static function checkHash($mail, $hash)
	{
		$res = Accounts::find(NULL, array("mail"=>$mail, "linkhash"=>$hash));
		if(count($res)==1)
		{
			$res[0]->setStatus("aktiv");
			$res[0]->save();
			return true;
		}
		return false;

	}
	public function register()
	{
		$mongo = $this->mongo;
		if($mongo == NULL)
		{
			$mongo = $this->mongo;
		}
		if($mongo == NULL)
		{
			throw new \Exception("No Connection to the MongoDB");
		}
		$this->setLinkHash($this->generateRandomString(40));
		//Remove ID if false
		$array = $this->toArray();
		if($array["_id"]==false)
		{
			unset($array["_id"]);
		}

		//UniID bei Neuen Records
		if(!isset($this->value["_id"]) || empty($this->value["_id"]))
		{
			if($this->checkUnique()!==true)
			{
				throw new \Exception("Unique error");
			}
		}
		$c = $mongo->selectCollection($this->name);
		$c->save($array);
		$array["url"] = \url::createURL("account/confirm/".base64_encode($this->getMail())."/".$this->getLinkHash());
		$mailContent = \SlimApp\PHPMailer::renderTemplate("account", "register", $array);
		$mail = \SlimApp\PHPMailer::getPHPMailer();
		$mail->addAddress($this->getMail());
		$mail->Subject=$mailContent["subject"];
		$mail->Body=$mailContent["body"];
		$mail->From = "bofh@byte.gs";
		#$mail->send();
		if(!$mail->send()) {
            #echo 'Message could not be sent.';
            #echo 'Mailer Error: ' . $p->ErrorInfo;
            throw new \Exception("Mail can't send: ".$mail->ErrorInfo, 1);
            
        } else {
            #echo 'Message has been sent';
        }

	}
	/*public function save()
	{
		throw new \Exception("Accounts don't have save, use register or update instand", 1);
		
	}*/
	private function generateRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, strlen($characters) - 1)];
	    }
	    return $randomString;
	}
}
