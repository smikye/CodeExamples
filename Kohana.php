<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Website_Brief extends Controller_Website{
	
	public function _template_save_to_reply($post = null,$name = null){
		$user = Auth::instance()->get_user();
        if($user != null){
            $this->_clean_temp($user->id);
        }else
		{
            return false;
        }
		
		if ($post)
		{
			$action = 'debug';
			$client = $post['brief_client'];
			$data = json_encode($post, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
			$data = str_replace( "\\", "", $data);
			$page = 'components/brief_page.php';
			
			$action = 'noaction';
			$today = date("Y-m-d H:i:s");
			
			$brief = ORM::factory('Brief')->where('name','=',$name)->where('user_id','=',$user->id)->find();
			$brief->brief = $data;
			$brief->save();
			$stat = $brief->saved();
			
			return $stat;
			
		}else
		{
			return false;
		}
		
		
		
	}

	public function action_test(){
		$brief = ORM::factory('BriefReply')->where('brief_id', '=', 58)->order_by('id', 'DESC')->limit(1)->find();
		$briefModel = new Model_CreativeBrief;

		/* function getBriefAnswers()
		 * This function is parse answers for the brief and return parsed Object
		 * 
		 * @param BriefAnswer $brief 
		 *	
		 * @return Object
		 */
		$answ = $briefModel->getBriefAnswers($brief);
		
		header('Content-Type: application/json');
    	print_r($answ);exit;
	}

	public function action_test2(){
		$brief = ORM::factory('Brief')->where('id', '=', 58)->order_by('id', 'DESC')->limit(1)->find();
		$briefModel = new Model_CreativeBrief;
		header('Content-Type: application/json');

		/* function getBriefData()
		 * This function is parse brief content and return parsed Object
		 * 
		 * @param Brief $brief 
		 *	
		 * @return Object
		 */
		print_r($briefModel->getBriefData($brief));exit;
	}

	public function action_test3(){
		
		$briefModel = new Model_CreativeBrief;

		/* function isArchived()
		 * This function check if brief is archived
		 * Return TRUE if brief is archived, FALSE if not
		 * 
		 * @param int 
		 *	
		 * @return boolean
		 */
		print_r($briefModel->isArchived(4));exit;
	}

	public function action_test4(){
		$brief = ORM::factory('BriefReply')->where('id', '=', 33)->limit(1)->find();

		/* function isLive()
		 * This function check if answer for the brief is in the live briefs
		 * Return TRUE if brief in the Live category, FALSE if WAITING TO APPROVAL
		 * 
		 * @param BriefAnswer $brief
		 *	
		 * @return boolean
		 */
		$briefModel = new Model_CreativeBrief;
		print_r($briefModel->isLive($brief));exit;
	}

	public function action_test5(){
		$brief = ORM::factory('Brief')->where('id', '=', 58)->limit(1)->find();

		$briefModel = new Model_CreativeBrief;

		/* function isTemplate()
		 * This function check if this brief is template and return TRUE if brief is template
		 * And return FALSE if brief is SAVED TO FINISH LATER
		 * 
		 * @param Brief $brief
		 *	
		 * @return boolean
		 */
		print_r($briefModel->isTemplate($brief));exit;
	}
    
    public function action_build($query = null,$type = null){
		
		$debug = $query == null ? $this->request->param('id') : $query;
		$tp = $type == null ? $this->request->param('type') : $type;
		$images = null;
		$brief_name = null;
		$preview = null;
		$fullname = $this->request->param('name');
		
		$briefModel = new Model_BriefAction;
		
		$user = Auth::instance()->get_user();
        if($user != null){
            $this->_clean_temp($user->id);
        } else {
            HTTP::redirect('/');
        }
		
		if ($debug){
				$dt = $debug;
				 
				if ($tp=='preview')
				{
					$preview = 1;
					
					$debug = str_replace('-',' ',$dt);
					
					$directory = DOCROOT.'uploads/briefs/'.($user->id).'/templates/'.$debug.'.txt';
					if (file_exists($directory)) {
						$brief_name = $debug;
						$images = ORM::factory('BriefTemplate')->where("user_id","=",$user->id)->where("brief_name","=",$debug)->find()->brief_files;
						$debug = gzdecode(file_get_contents($directory));
						}
					else
					{
						$directory = DOCROOT.'uploads/briefs/'.($user->id).'/templates/'.$dt.'.txt';
						if (file_exists($directory)) {
							$brief_name = $dt;
							$images = ORM::factory('BriefTemplate')->where("user_id","=",$user->id)->where("brief_name","=",$dt)->find()->brief_files;
							$debug = gzdecode(file_get_contents($directory));
							}
					}
				}else
				if ($tp=='edit')
				{
					$dt = $debug;
					$debug = str_replace('-',' ',$debug);
					$directory = DOCROOT.'uploads/briefs/'.($user->id).'/templates/'.$debug.'.txt';
					if (file_exists($directory)) {
						$brief_name = $debug;
						$images = ORM::factory('BriefTemplate')->where("user_id","=",$user->id)->where("brief_name","=",$debug)->find()->brief_files;
						$debug = gzdecode(file_get_contents($directory));
						}
					else
					{
						$directory = DOCROOT.'uploads/briefs/'.($user->id).'/templates/'.$dt.'.txt';
						if (file_exists($directory)) {
							$brief_name = $dt;
							$images = ORM::factory('BriefTemplate')->where("user_id","=",$user->id)->where("brief_name","=",$dt)->find()->brief_files;
							$debug = gzdecode(file_get_contents($directory));
							}
					}
				}else HTTP::redirect('/');
			}		
		
		$action = 'show';
		$data = '';
		$page = 'components/brief_design.php';
		$post = $this->request->post();
		
		if ($post)
		{
				
			$post = $briefModel->save_post_images($post,$_FILES,$user);
			
			$action = 'debug';
			$client = $post['brief_client'];
			$page = 'components/brief_page.php';
			$action = 'noaction';
			
			$stat = $briefModel->brief_save_before_send($post,$user,$client);
			$stat = $briefModel->brief_send($post,$user,$client);
			
			
			$data = '<script>
			window.parent.popup('.$stat.',1);
			</script>';
			
			$this->view = View::Factory('website/brief')
			->bind('action', $action)
			->bind('data', $data)
			->bind('page_file', $page)
			->bind('debug', $debug)
			->bind('status', $data)
			->bind('preview', $preview);
			
		}else
		{
		$query = ORM::factory('Profile')->where('user_id', '=', $user->id)->find();//$user->id
		$logo = $query->photo();
		$fullname = $user->id;
		
        $this->view = View::Factory('website/brief')
			->bind('action', $action)
			->bind('data', $data)
			->bind('images', $images)
			->bind('page_file', $page)
			->bind('debug', $debug)
			->bind('logo', $logo)
			->bind('preview', $preview)
			->bind('brief_name', $brief_name)
			->bind('name', $fullname);
		}
    }
	
	public function action_noname($query = null, $type = null, $dt = null){
		$result = $query == null ? $this->request->param('id') : $query;
		$this->action_replybrief($result);
	}
	
	public function action_template($query = null, $type = null, $dt = null){
		$result = $query == null ? $this->request->param('id') : $query;
		$result2 = $type == null ? $this->request->param('type') : $type;
		$id = $dt == null ? $this->request->param('name') : $td;
		
		$post = $this->request->post();
		$user = Auth::instance()->get_user();
		
		if ($post)
		{
			$this->action_preview();
		}else
		{	
		if ($result && $result2) $this->action_build($query,$type); else
		if ($result && $result2==null && $id)
			{
				$result = str_replace('-',' ',$result);
				$brief = ORM::factory('Brief')->where('name','=',$result)->where('user_id','=',$id)->find();
				if ($brief->loaded())
				{
					$this->action_replybrief($brief->id);
				} else HTTP::redirect('/');
			} else HTTP::redirect('/');
		}
		
    }
	
	public function action_reply($query=null,$head = false, $review = false){
		$id = $query == null ? $this->request->param('id') : $query;
		$this->action_read($id, $head, $review);
	}
	
	public function action_approved($query=null){
		$id = $query == null ? $this->request->param('id') : $query;
		$this->action_read($id);
	}
	
	public function action_read($query = null,$pageAct = null, $review = false){
		
		$id = $query == null ? $this->request->param('id') : $query;
		$user = Auth::instance()->get_user();
		$query = ORM::factory('Profile')->where('user_id', '=', $user->id)->find();//$user->id
		$logo = $query->photo();
		
		$id2 = ORM::factory('BriefReply')->where('id', '=', $id)->find();
		$from_client = ORM::factory('Brief')->where('id', '=', $id2->brief_id)->find();//$user->email
		if ($from_client->user == $user->email)
		{
		
		$data = $from_client->brief;
		if ($id2->approve) $id = 0;
		
		if ($data) $from_client = $data;

		$action = 'read';
		$debug = $from_client;
		$page = 'components/brief_view.php';
		$preview = null;
		$data = null;
		$pth = '../uploads/briefs/'.($query->user_id).'/';
		
		$this->action_preview($from_client,$id2->brief,$id2->date, $id, $pageAct, $review);
		}else
		{
			HTTP::redirect('/');
		}
	}
	
	public function action_creative_briefs($query = null){
		
		
		$debug = $query == null ? $this->request->param('id') : $query;
		
		$user = Auth::instance()->get_user();
        if($user != null){
			$directory = DOCROOT.'uploads/briefs/'.($user->id).'/templates/';
			if (!file_exists($directory)) $data = ''; else
			{
				$files = scandir($directory);
				$templates = [];
				foreach($files as $file){
					if ($file!='.' && $file!='..'){
						$templates[] = substr($file, 0, -4);
					}
				}
				
			}
			$templates = ORM::factory('BriefTemplate')->where("user_id","=",$user->id)->find_all();
			
			$from_client = ORM::factory('BriefReply')->where('sender', '=', $user->email)->find_all();
			$to_client = ORM::factory('Brief')->where('user_id', '=', $user->id)->find_all();
			$archived = ORM::factory('BriefArchived')->where('user_id', '=', $user->id)->find_all();
			
			$types = array('Template','Saved to finish later','Live Creative Brief','Awaiting approval');
			
        }else
		{
            HTTP::redirect('/');
        }
		
		$action = 'noaction';
		
		$page = 'components/brief_all.php';
		$post = $this->request->post();
		
		
		$query = ORM::factory('Profile')->where('user_id', '=', $user->id)->find();//$user->id
		$logo = $query->photo();
		$fullname = $user->full_name;
		
        $this->view = View::Factory('website/brief')
			->bind('action', $action)
			->bind('templates', $templates)
			->bind('from_client', $from_client)
			->bind('to_client', $to_client)
			->bind('archive', $archived)
			->bind('types', $types)
			->bind('page_file', $page)
			->bind('debug', $debug)
			->bind('logo', $logo)
			->bind('fullname', $fullname)
			->bind('user', $user)
			->bind('category', $debug);
		
    }
	
	public function action_approve($query = null){
		$debug = $query == null ? $this->request->param('id') : $query;
		$user = Auth::instance()->get_user();
		
		if ($debug){
			$brief = ORM::factory('BriefReply')->where('id', '=', $debug)->find();
			if (!$brief->approve)
			{
				$briefModel = new Model_BriefAction;
				$briefModel->brief_approved_notification($brief->brief);
				$brief = ORM::factory('BriefReply',$debug);
				$brief->approve = 1;
				$brief->save();
				
				
				
				HTTP::redirect('/creative-brief');
			}
			
		}else
		{
			HTTP::redirect('/creative-brief');
		}
		
	}
	
	public function action_share(){
		$post = $this->request->post();
		$user = Auth::instance()->get_user();
		
		if($post && $user)
		{
			$briefModel = new Model_BriefAction;
			$brief = ORM::factory('Brief')->where('id', '=', $post['id'])->find();
			$title = json_decode($brief->brief, true);
			$result = $briefModel->brief_mail_notification($title['title_brief_info'], $user, $post['client'], $post['link']);
			if ($result) $result = 'true'; else $result = 'false';
			
			$this->response->headers('Content-Type', 'application/json; charset=utf-8');
			$this->response->body($result);
		}
		
		
	}
	
	public function action_delete(){
		$id = $this->request->param('id');
		$type = $this->request->param('type');
		$user_id = $this->request->param('name');
		
		$user = Auth::instance()->get_user();
		
		if ($user->id==$user_id){
			$result = 0;
			$category = '';
			switch($type){
				//Templates
				case 0:
					$brief = ORM::factory('BriefTemplate')->where('id', '=', $id)->find();
					if ($brief->loaded()){
						$path = DOCROOT.'uploads/briefs/'.$user->id.'/templates/'.$brief->brief_name.'.txt';
						if (file_exists($path))  unlink($path);
						
						$archive = ORM::factory('BriefArchived')->where('brief_id', '=', $id)->where('brief_name', '=', $brief->brief_name)->find();
						if ($archive->loaded()) $archive->delete();
						$brief->delete();
						$category = '/4';
					}
				break;
				//To client
				case 1:
					$brief = ORM::factory('Brief')->where('id', '=', $id)->where('user_id', '=', $user_id)->find();
					if ($brief->loaded()){
						
						$archive = ORM::factory('BriefArchived')->where('brief_id', '=', $id)->where('user_id', '=', $id)->find();
						if ($archive->loaded()) $archive->delete();
						$brief->delete();
						$category = '/3';
					}
				break;
				//Live
				case 2:
					$brief = ORM::factory('BriefReply')->where('id', '=', $id)->find();//->where('user_id', '=', $user_id)
					if ($brief->loaded()){
						$archive = ORM::factory('BriefArchived')->where('brief_id', '=', $id)->where('user_id', '=', $id)->find();
						if ($archive->loaded()) $archive->delete();
						$brief->delete();
						$category = '/1';
					}
				break;
				// Answers
				case 3:
					$brief = ORM::factory('BriefReply')->where('id', '=', $id)->find();//->where('user_id', '=', $user_id)
					if ($brief->loaded()){
						$archive = ORM::factory('BriefArchived')->where('brief_id', '=', $id)->where('user_id', '=', $id)->find();
						if ($archive->loaded()) $archive->delete();
						$brief->delete();
						$category = '/2';
					}
				break;
				//Archived
				case 4:
					
						$archive = ORM::factory('BriefArchived')->where('id', '=', $id)->where('user_id', '=', $user_id)->find();
						if ($archive->loaded()){
							switch($archive->brief_type)
							{
								//Templates
								case 0:
									$brief = ORM::factory('BriefTemplate')->where('id', '=', $archive->brief_id)->find();
									if ($brief->loaded()){
										$path = DOCROOT.'uploads/briefs/'.$user->id.'/templates/'.$brief->brief_name.'.txt';
										if (file_exists($path))  unlink($path);
										$brief->delete();
									}
								break;
								//To client
								case 1:
									$brief = ORM::factory('Brief')->where('id', '=', $archive->brief_id)->where('user_id', '=', $user_id)->find();
									if ($brief->loaded()){
										$brief->delete();
									}
								break;
								//Live
								case 2:
									$brief = ORM::factory('BriefReply')->where('brief_id', '=', $archive->brief_id)->where('user_id', '=', $user_id)->find();
									if ($brief->loaded()){
										$brief->delete();
									}
								break;
								//Answers
								case 3:
									$brief = ORM::factory('BriefReply')->where('brief_id', '=', $archive->brief_id)->where('user_id', '=', $user_id)->find();
									if ($brief->loaded()){
										$brief->delete();
									}
								break;
								
							}
							
							$archive->delete();
						}
					
				break;
				
			}
			
			
		}
		
		HTTP::redirect('/creative-brief');
		
		
	}
	
	public function action_archive(){
		$id = $this->request->param('id');
		$type = $this->request->param('type');
		$user_id = $this->request->param('name');
		
		$user = Auth::instance()->get_user();
		
		if ($user->id==$user_id){
			
			$result = 0;
			switch($type){
				//Templates
				case 0:
					$brief = ORM::factory('BriefTemplate')->where('id', '=', $id)->find();
					if ($brief){
						$brief->archived = 1;
						$brief->save();
						$archive = ORM::factory('BriefArchived');
						$archive->brief_type = $brief->method;
						$archive->brief_id = $id;
						$archive->brief_category = $brief->brief_type;
						$archive->brief_name = $brief->brief_name;
						$archive->user_id = $user_id;
						$archive->save();
						if($archive->saved()) $result = 1;
					}
				break;
				//Live
				case 2:
					$brief = ORM::factory('BriefReply')->where('id', '=', $id)->find();
					if ($brief){
						$brief->archived = 1;
						$brief->save();
						$archive = ORM::factory('BriefArchived');
						$archive->brief_type = 2;
						$archive->brief_id = $id;
						
						$data = json_decode($brief->brief,true);
						$archive->brief_category = $data['project_type'];
						
						$archive->brief_name = $brief->name;
						$archive->user_id = $user_id;
						$archive->save();
						if($archive->saved()) $result = 1;
					}
				break;
				// Answers
				case 3:
					$brief = ORM::factory('BriefReply')->where('id', '=', $id)->find();
					if ($brief){
						$brief->archived = 1;
						$brief->save();
						$archive = ORM::factory('BriefArchived');
						$archive->brief_type = 3;
						$archive->brief_id = $id;
						$archive->brief_name = $brief->name;
						$archive->user_id = $user_id;
						$archive->save();
						if($archive->saved()) $result = 1;
					}
				break;
				
			}
			
			
		}
		
		HTTP::redirect('/creative-brief');
		
	}
	
	public function action_unarchive(){
		$id = $this->request->param('id');
		$user_id = $this->request->param('name');
		
		$user = Auth::instance()->get_user();
		
		if ($user && $user->id==$user_id){
			
			$result = 0;
			$brief = ORM::factory('BriefArchived')->where('id', '=', $id)->find();
			
			switch($brief->brief_type){
				//Templates
				case 0:
				case 1:
					$archive = ORM::factory('BriefTemplate')->where('id', '=', $brief->brief_id)->find();
					if ($archive){
						$archive->archived = 0;
						$archive->save();
						
						$brief->delete();
						if($archive->saved()) $result = 1;
					}
				break;
				//Live
				case 2:
					$archive = ORM::factory('BriefReply')->where('id', '=', $brief->brief_id)->find();
					if ($archive){
						$archive->archived = 0;
						$archive->save();
						
						$brief->delete();
						if($archive->saved()) $result = 1;
					}
				break;
				
			}
			
			
		}
		
		HTTP::redirect('/creative-brief');
		
	}
    
	
    public function action_preview($data_brief = null,$data_answer = null,$date = null,$brief_number = null,$pageAction = false,$review = false){
		
		if (!$brief_number) $brief_number = 0;
		
		$user = Auth::instance()->get_user();
        if($user != null){
			$this->_clean_temp($user->id);			 
        }
			
		$path = 'uploads/briefs/'.($user->id ).'/';//$user->id
		if (!file_exists($path)) { mkdir($path, 0755, true);}
		
		$briefModel = new Model_BriefAction;

		$action = 'show';
		$data = '';
		$debug = [];
		$page = 'components/brief_design.php';
		$post = $this->request->post();
		
		if ($post)
		{	
			$post = $briefModel->save_post_images($post,$_FILES,$user,'temp');
			
			$action = 'debug';
			$data = json_encode($post, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);//
			
			$data = str_replace( "\\n", "&#013;&#010;", $data);
			$data = str_replace( "\\", "", $data);
			$page = 'components/brief_view.php';
			
			$sSearch='element_image_input';
			
		}else{
			$action = 'debug';
			$page = 'components/brief_view.php';
			if ($data_brief) {$data = $data_brief;$action =  'read';}
			
        }
		$query = ORM::factory('Profile')->where('user_id', '=', $user->id)->find();//$user->id
		$logo = $query->photo();
		$preview = null;
		$id = $user->id;
		
        $this->view = View::Factory('website/brief')
			->bind('action', $action)
			->bind('name', $action)
			->bind('data', $data)
			->bind('page_file', $page)
			->bind('debug', $debug)
			->bind('user_id', $id)
			->bind('review', $pageAction)
			->bind('path', $path)
			->bind('logo', $logo)
			->bind('answer', $data_answer)
			->bind('date', $date)
			->bind('brief', $brief_number)
			->bind('preview', $preview)
            ->bind('isReview', $review);
        
    }
	
	
	protected function _clean_temp($id){
		$directory = DOCROOT.'uploads/briefs/'.$id.'/';
		$arr = [];	
		if (!file_exists($directory)) { return false;}
		$files = scandir($directory);
		
		foreach($files as $file){
			if ($file!='.' && $file!='..' && $file!='templates'){
				$type = explode("_", $file);
				if ($type[0]!="full"){
					$arr[] = unlink($directory.$file);
				}
			}
		}
		
		return $arr;
	}
	
	public function action_template_save(){
		
		$post = $this->request->post();
		
		$user = Auth::instance()->get_user();

		if ($post)
		{
			$gzdata = gzencode($post['template'], 5);
			$file = $post['name'].'.txt';
			$rewrite = false;
			
			$path = 'uploads/briefs/'.($user->id ).'/';//$user->id
			if (!file_exists($path)) { mkdir($path, 0755, true);}
			
			$directory = DOCROOT.'uploads/briefs/'.($user->id ).'/templates/';
			if (!file_exists($directory)) { mkdir($directory, 0755, true);}
			$directory = DOCROOT.'uploads/briefs/'.($user->id ).'/templates/'.$file;
			if (file_exists($directory)) { $rewrite = true;}
			$result = file_put_contents($directory, $gzdata);
			if ($result===false) $result = 0; else $result = 1;
			
			if(!$rewrite){
				$brief = ORM::factory('BriefTemplate');
				$brief->user_id = $user->id;
				$brief->brief_name = $post['name'];
				$brief->brief_type = $post['project'];
				$brief->method = $post['method'];
				$brief->save();
				$result = $brief->saved();
			}else
			{
				$name = $post['name'];
				$resave = false;
				$brief = ORM::factory('BriefTemplate')->where('user_id','=',$user->id)->where('brief_name','=',$name)->find();//->where("brief_name","=",$name)
				if ($brief->brief_type != $post['project']) {$brief->brief_type = $post['project']; $resave = true;}
				if ($brief->method != $post['method']) {$brief->method = $post['method']; $resave = true;}
				if ($resave){
				$brief->save();
				$result = $brief->saved();
				}else $result = true;
				
			}
			
			$name = $post['name'];
			$brief = ORM::factory('Brief')->where('name','=',$name)->where('user_id','=',$user->id)->find();
			if ($brief->loaded())
			{
				// Load was successful
				$today = date("Y-m-d H:i:s");
				
				$brief->user = $user->email;
				$brief->date = $today;
				$brief->status = 1;
				$brief->save();
				$stat = $brief->saved();
				if ($stat===false) $result = 0; else $result = 1;
				
			}
			else
			{
				// Error
				$brief = ORM::factory('Brief');
				
				$today = date("Y-m-d H:i:s");
				$brief->name = $name;
				$brief->user_id = $user->id;
				$brief->user = $user->email;
				$brief->date = $today;
				$brief->status = 1;
				$brief->save();
				$stat = $brief->saved();
				if ($stat===false) $result = 0; else $result = 1;
				
			}
			
			if ($result===false) $result = 0; else $result = 1;
			
			$this->auto_render = false;
			$this->is_ajax = TRUE;
			$this->request->headers['Content-Type'] = 'application/json';
			$this->response->body( $result );
		}else
		{
			$result = 0;
            $this->auto_render = false;
			$this->is_ajax = TRUE;
			$this->request->headers['Content-Type'] = 'application/json';
			$this->response->body( $result );
        }
		
	}
	
	public function action_template_update($query=null){
		$name = $query == null ? $this->request->param('id') : $query;
		$post = $this->request->post();
		
		$user = Auth::instance()->get_user();
        $result = 1;
		
		if ($post)
		{
			
			$brief = ORM::factory('BriefTemplate')->where("user_id","=",$user->id)->where("brief_name","=",$name)->find();
			$files = array();
			if (isset($_FILES['brief_logo'])){
				$upload = $this->_save_image($_FILES['brief_logo'],$user->id ,'full');
				if ($upload) $files['brief_logo'] = $upload; else if (isset($post['brief_logo'])) $files['brief_logo'] = $post['brief_logo'];
			}else if (isset($post['brief_logo'])) $files['brief_logo'] = $post['brief_logo'];
			
			$names = array_keys($post);
			foreach ($names as $key => $value) {
				$im = explode("_", $value);
				if (($im[0]=="element") && ($im[1]=="image") && ($im[2]=="text")) 
				{
					$image = $_FILES["element_image_input_".$im[3]."_".$im[4]];
					
					if (isset($image))
					{
						$filename = $this->_save_image($image,$user->id ,'full');//$user->id
						if ($filename) $files["element_image_input_".$im[3]."_".$im[4]] = $filename;
						else if (isset($post["element_image_input_".$im[3]."_1"]) && $post["element_image_input_".$im[3]."_1"]!="" ) $files["element_image_input_".$im[3]."_".$im[4]] = $post["element_image_input_".$im[3]."_1"];
					} else if (isset($post["element_image_input_".$im[3]."_1"]) && $post["element_image_input_".$im[3]."_1"]!="" ) $files["element_image_input_".$im[3]."_".$im[4]] = $post["element_image_input_".$im[3]."_1"];
					
					
				}
			}
			
			if (count($files)!=0)
			{
				$data = json_encode($files, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
				
				$brief->brief_files = $data;
				$brief->save();
				
				if ($brief->saved()) $result = 1; else $result = 0;
			}
			
			$post = array_merge($post, $files);
			$result = $this->_template_save_to_reply($post,$name);
			if ($result==null) $result = 0;
			
			$data = '<script>
			window.parent.popup('.$result.',0);
			</script>';
			
			
			$this->auto_render = false;
			$this->is_ajax = TRUE;
			$this->request->headers['Content-Type'] = 'application/json';
			$this->response->body( $data );
			
		}else
		{
			$result = 0;
            $this->auto_render = false;
			$this->is_ajax = TRUE;
			$this->request->headers['Content-Type'] = 'application/json';
			$this->response->body( $result );
        }
		
	}
	
	public function action_template_load(){
		
		$post = $this->request->post();
		$user = Auth::instance()->get_user();
		
		if ($post)
		{
			$file = 'template.txt';
			$directory = DOCROOT.'uploads/briefs/'.(5).'/templates/';
			if (!file_exists($directory)) { mkdir($directory, 0755, true);}
			$directory = DOCROOT.'uploads/briefs/'.(5).'/templates/'.$file;
			
			$result = gzdecode(file_get_contents($directory));
			
			$this->auto_render = false;
			$this->is_ajax = TRUE;
			$this->request->headers['Content-Type'] = 'application/json';
			$this->response->body( $result );
		}else
		{
            HTTP::redirect('/');
        }
		
	}
	
	public function action_feedback($query = null){
		
		$id = $query == null ? $this->request->param('name') : $query;
		$user = Auth::instance()->get_user();
		$context = array();
		
		if ($id && $user){
			$action = 'feedback';
			$debug = [];
			$page = 'components/brief_feedback.php';
			$brief = ORM::factory('BriefReply')->where('id', '=', $id)->find();

            $brief_data = json_decode($brief->brief, true);

            foreach ($brief_data as $key => $value) {
                if (ctype_upper(substr($key, 0, strpos($key, "_")))) {
                    $context[str_replace("_", " ", $key)] = $value;
                }
                if (($key == 'gender') || ($key == 'age') || ($key == 'industry') || ($key == 'job')) {
                    if($key == 'industry'){
                        $industry = ORM::factory('Industry')->where('id', '=', $value)->find();
                        $context['client_data'][] = $industry->name;
                    } else if($key == 'job'){
                        $job = ORM::factory('Position')->where('id', '=', $value)->find();
                        $context['client_data'][] = $job->name;
                    } else {
                        $context['client_data'][] = $value;
                    }
                }
            }

			if ($brief)
			{
			if ($brief->sender==$user->email)
				{
				$this->view = View::Factory('website/brief')
					->bind('action', $action)
					->bind('page_file', $page)
					->bind('debug', $debug)
                    ->bind('context', $context)
					->bind('brief', $brief);
				}else
				{
					HTTP::redirect('/');
				}	
			}else
			{
				HTTP::redirect('/');
			}	
		}else
		{
			HTTP::redirect('/');
		}	
		
		
	}
	
	
	public function action_replybrief($query = null){
		
		$post = $this->request->post();
		$debug = $query == null ? $this->request->param('id') : $query;
		$id = $this->request->param('name');
		$briefModel = new Model_BriefAction;
		
		$brief_data_id = 0;
		
		if (is_string($debug) && !is_numeric($debug)){
			$debug = str_replace('-',' ',$debug);
			$brief = ORM::factory('Brief')->where('name','=',$debug)->where('user_id','=',$id)->find();
				if ($brief->loaded())
				{
					$debug = $brief->id;
				} else HTTP::redirect('/');
		}
		
		$user = Auth::instance()->get_user();
		$query = ORM::factory('Brief')->where('id', '=', $debug)->find();
		
		if ($post)
		{
			$post = $briefModel->save_post_images($post,$_FILES,$user);
			
			if (isset($_FILES["user_doc"]))
				{
					$filename = $briefModel->save_file($_FILES["user_doc"],$user->id,'full');
					if ($filename) $post["user_doc"] = $filename;
				}
			
			$data = json_encode($post, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
			$data = str_replace( "\\n", "#sNew", $data);
			$data = str_replace( "\\", "", $data);
			$brief = ORM::Factory('BriefReply');
			$brief->user_id = $id;//$user->id;
			$brief->brief = $data;
			$brief->name = $post['project_name'];
			$brief->sender = $post['brief_client'];
			$brief->brief_id = $debug;
			$brief->date = date("Y-m-d");
			$brief->save();
			$stat = $brief->saved();

			$subject = 'Desinion Creative Brife';
			$body = "You have a new creative brief waiting your approval.<br>To view this Creative Brief, click on the ";
			$body .= "<a href='";
			$body .= "http://desinion.pandaaa.bplaced.net/creative-brief";
			$body .= "'>link</a>";
			$to = $post['brief_client'];
			
			$welcome_email = Email::factory($subject)
                    ->message($body, 'text/html')        
                    ->from('noreply@desinion.com', 'Desinion')
                    ->to($post['brief_client']);
					
			$stat = $welcome_email->send();
			if ($stat) $stat = 1; else $stat = 0;
			
			$data = '<script>
			window.parent.dialogSend('.$stat.',"/creative-brief/'.$brief->id.'/review/'.$brief->id.'");
			</script>';
			
			
			$page = 'components/brief_page.php';
			$action = 'noaction';
			$preview = null;
			
			$this->view = View::Factory('website/brief')
			->bind('action', $action)
			->bind('data', $data)
			->bind('page_file', $page)
			->bind('debug', $debug)
			->bind('status', $data)
			->bind('preview', $preview);
			
			
		}else
		{
        if ( !$debug || !$query){
            HTTP::redirect('/');
        }
		
		$query = ORM::factory('Brief')->where('id', '=', $debug)->find();
		$data = $query->brief;
		$request = $query->user;
		
		if (!$query->status){
		$brief = ORM::factory('Brief',$query->id);
		$brief->status = 1;
		$brief->save();
		}
		
		$name = ORM::factory('User')->where('id', '=', $query->user_id)->find()->full_name;

		$pth = '../uploads/briefs/'.($query->user_id).'/';
		$query = ORM::factory('Profile')->where('user_id', '=', $query->user_id)->find();
		$logo = $query->photo();
		
		$action = 'reply';

		$page = 'components/brief_view.php';
		
		
        $this->view = View::Factory('website/brief')
			->bind('action', $action)
			->bind('data', $data)
			->bind('page_file', $page)
			->bind('debug', $debug)
			->bind('path', $pth)
			->bind('logo', $logo)
			->bind('user', $name)
			->bind('request', $request);
		}
    }
	
	public function action_review($query = null){
		$id = $query == null ? $this->request->param('id') : $query;
		$this->action_reply($id, true, true);//,'review'
    }
	
	
	protected function _save_image($image,$id,$type='temp')
    {
        if (
            ! Upload::valid($image) OR
            ! Upload::not_empty($image) OR
            ! Upload::type($image, array('jpg', 'jpeg', 'png', 'gif')))
        {
            return FALSE;
        }
 
        $directory = DOCROOT.'uploads/briefs/'.$id.'/';
		if (!file_exists($directory)) { mkdir($directory, 0755, true);}

        {
			$img = explode(".", $image['name']);
			$temp = $type.'_'.date("Y-m-d-H-i-s").$image['name'];
            $filename = $type.'_'.strtolower(Text::random('alnum', 20)).$image['name'];//'.png';
			
			move_uploaded_file($image['tmp_name'], $directory.$temp);
            
            return $temp;
			
			
			
        }
 
        return FALSE;
    }
    
	protected function _save_file($file,$id,$type='temp')
    {
		
        if (
            ! Upload::valid($file) OR
            ! Upload::not_empty($file))
        {
            return FALSE;
        }
 
        $directory = DOCROOT.'uploads/briefs/'.$id.'/';
		if (!file_exists($directory)) { mkdir($directory, 0755, true);}

        {
			$img = explode(".", $file['name']);
			$temp = $type.'_'.date("Y-m-d-H-i-s").$file['name'];
            $filename = $type.'_'.strtolower(Text::random('alnum', 20)).$file['name'];//'.png';
			
			move_uploaded_file($file['tmp_name'], $directory.$temp);
            
            return $temp;
			
			
			
        }
 
        return FALSE;
    }
    
}
