<?php

class  Infinitech_Weixinpay_Model_Sdkruntimeexception extends Exception {
	public function errorMessage()
	{
		return $this->getMessage();
	}

}

?>