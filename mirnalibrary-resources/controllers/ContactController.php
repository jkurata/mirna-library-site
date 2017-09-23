<?php 
	class ContactController extends Controller{
		public function index(){
			$config = include(RESOURCE_PATH .'/config.php');
			// Must pass in variables (as an array) to use in template
			$variables = array(
				'contact_class' => 'active',
				'bodyArray' => array("sitekey" => $config["captcha"]["sitekey"]),
				'footerArray' => array("other-scripts" => "<script src='https://www.google.com/recaptcha/api.js'></script>")
			);
			
			// defined in templateFunction.php
    		renderLayoutWithContentFile("contact_form", $variables);
		}
		
		public function submitted(){
			require_once LIBRARY_PATH.'/EmailComment.php';
			$comment = new EmailComment();
			$success = $comment->send_comment();
			if ($success){
				$bodyArray = array("status-message"=>"Thank you for your message!", "display"=>
				"display:none;");
			}else{
				$bodyArray = array("status-message"=>"Unfortunately, your message could not be sent.",
				"display"=>"");
			}
			
			// Must pass in variables (as an array) to use in template
			$variables = array(
				'bodyArray' => $bodyArray
			);
			
			// defined in templateFunction.php
    		renderLayoutWithContentFile("submitted_contact", $variables);
		}
	}
?>