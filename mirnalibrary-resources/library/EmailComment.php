<?php

	class EmailComment{

		
		function __construct(){
			$name = $_POST["name"];
			$email = $_POST["email"];
			$comment = $_POST["comment"];
			$this->captcha = $_POST["g-recaptcha-response"];

			/* Load captcha and email information */
			$this->config = include(RESOURCE_PATH ."/config.php");
			
			$this->from = "From: ".$name;  
			$this->subject = "miRNA Library Comment";
			
			$this->body = "From: ".$name."\nEmail: ".$email."\n Message: \n".$comment;
		}
		
		function send_comment(){
			if (! isset($this->captcha)){
				return False;
			}
			$response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".
									$this->config["captcha"]["secretkey"]."&response=".$this->captcha."&remoteip=".$_SERVER["REMOTE_ADDR"]), True);
			if ($response["success"] != True){
				return False;
			}
			if (mail($this->config["email"]["address"], $this->subject, $this->body, $this->from)){
				return True;
			}else{
				return False;
			}
		}
	}

?>