<?php
	# Load

	$Config = Config::Get('Borg');

	if(!is_array($Config))
	{
		Std::Out('[Warning] [Borg] Config is empty. Unloading Event::text');

		$ModuleManager->UnloadModule('Event', 'text');

		return;
	}

	if(empty($Config['PVReplyRate']) || !is_int($Config['PVReplyRate']) || $Config['PVReplyRate'] < 0 || $Config['PVReplyRate'] > 100)
	{
		Std::Out('[Warning] [Borg] Config[PVReplyRate] must be an integer (0-100%). Unloading Event::text');

		$ModuleManager->UnloadModule('Event', 'text');

		return;
	}

	if(empty($Config['NickReplyRate']) || !is_int($Config['NickReplyRate']) || $Config['NickReplyRate'] < 0 || $Config['NickReplyRate'] > 100)
	{
		Std::Out('[Warning] [Borg] Config[NickReplyRate] must be an integer (0-100%). Unloading Event::text');

		$ModuleManager->UnloadModule('Event', 'text');

		return;
	}

	if(empty($Config['ReplyRate']) || !is_int($Config['ReplyRate']) || $Config['ReplyRate'] < 0 || $Config['ReplyRate'] > 100)
	{
		Std::Out('[Warning] [Borg] Config[ReplyRate] must be an integer (0-100%). Unloading Event::text');

		$ModuleManager->UnloadModule('Event', 'text');

		return;
	}

	# Execute

	if((!$Message->IsGroupMessage() && $Config['PVReplyRate'] > 0 && rand(1, 100) <= $Config['PVReplyRate']) || (strpos(strtolower($Message->Text), strtolower($WhatsBot->GetNickname())) !== false && $Config['NickReplyRate'] > 0 && rand(1, 100) <= $Config['NickReplyRate']) || ($Config['ReplyRate'] > 0 && rand(1, 100) <= $Config['ReplyRate']))
	{
		try
		{
			$Response = Unirest\Request::post('https://borg.polsaker.me/nisman', array(), http_build_query(array('source' => $Message->Name, 'message' => $Message->Text)));

			if($Response->code === 200)
			{
				$Response = json_decode($Response->raw_body, true);

				if($Response !== null)
				{
					if(isset($Response['status']) && $Response['status'] == 'ok' && !empty($Response['reply']))
					{
						$this->WhatsApp->SendRawMessage($Message->From, $Response['reply']);

						return true;
					}
					else
						Std::Out("[Warning] [Borg] Borg server died :P ($Message->Name : $Message->Text)");
				}
				else
					Std::Out("[Warning] [Borg] Borg server died (not json response) :P ($Message->Name : $Message->Text)");
			}
			else
				Std::Out("[Warning] [Borg] Borg server died (HTTP {$Response->code}) :P ($Message->Name : $Message->Text)");
		}
		catch(Exception $Exception)
		{
			Std::Out('[Warning] [Borg] ' . get_class($Exception) . ': ' . $Exception->getMessage());
		}

		$this->WhatsApp->SendMessage($Message->From, 'message:borg_server_died');
	}