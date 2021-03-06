<?php
/********************************************************************************* 
 *  This file is part of Sentrifugo.
 *  Copyright (C) 2014 Sapplica
 *   
 *  Sentrifugo is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Sentrifugo is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Sentrifugo.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  Sentrifugo Support <support@sentrifugo.com>
 ********************************************************************************/

class Default_AppraisalquestionsController extends Zend_Controller_Action
{

    private $options;
	public function preDispatch()
	{
	}
	
    public function init()
    {
        $this->_options= $this->getInvokeArg('bootstrap')->getOptions();
    }

    public function indexAction()
    {
		$appraisalquestionsmodel = new Default_Model_Appraisalquestions();	
        $call = $this->_getParam('call');
		if($call == 'ajaxcall')
				$this->_helper->layout->disableLayout();
		
		$view = Zend_Layout::getMvcInstance()->getView();		
		$objname = $this->_getParam('objname');
		$refresh = $this->_getParam('refresh');
		$dashboardcall = $this->_getParam('dashboardcall');
		
		$data = array();
		$searchQuery = '';
		$searchArray = array();
		$tablecontent='';
		
		if($refresh == 'refresh')
		{
		    if($dashboardcall == 'Yes')
				$perPage = DASHBOARD_PERPAGE;
			else	
				$perPage = PERPAGE;
			$sort = 'DESC';$by = 'aq.modifieddate';$pageNo = 1;$searchData = '';$searchQuery = '';$searchArray='';
		}
		else 
		{
			$sort = ($this->_getParam('sort') !='')? $this->_getParam('sort'):'DESC';
			$by = ($this->_getParam('by')!='')? $this->_getParam('by'):'aq.modifieddate';
			if($dashboardcall == 'Yes')
				$perPage = $this->_getParam('per_page',DASHBOARD_PERPAGE);
			else 
				$perPage = $this->_getParam('per_page',PERPAGE);
			$pageNo = $this->_getParam('page', 1);
			/** search from grid - START **/
			$searchData = $this->_getParam('searchData');	
			$searchData = rtrim($searchData,',');
			/** search from grid - END **/
		}
				
		$dataTmp = $appraisalquestionsmodel->getGrid($sort, $by, $perPage, $pageNo, $searchData, $call, $dashboardcall);		 		
					
		array_push($data,$dataTmp);
		$this->view->dataArray = $data;
		$this->view->call = $call ;
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
		
		$this->render('commongrid/index', null, true);
		
    }
	
	public function addAction()
	{
	   $auth = Zend_Auth::getInstance();
     	if($auth->hasIdentity()){
					$loginUserId = $auth->getStorage()->read()->id;
					$loginuserRole = $auth->getStorage()->read()->emprole;
					$loginuserGroup = $auth->getStorage()->read()->group_id;
		}
		$callval = $this->getRequest()->getParam('call');
		if($callval == 'ajaxcall')
			$this->_helper->layout->disableLayout();
		$appraisalquestionsform = new Default_Form_Appraisalquestions();
		$appraisalCategoryModel = new Default_Model_Appraisalcategory();
		$msgarray = array();
		$popConfigPermission = array();
		
	 	if(sapp_Global::_checkprivileges(APPRAISALQUESTIONS,$loginuserGroup,$loginuserRole,'add') == 'Yes'){
	 		array_push($popConfigPermission,'appraisalquestions');
	 	}
	 	
	 	$appraisalCategoriesData = $appraisalCategoryModel->getAppraisalCategorysData();
	 		if(sizeof($appraisalCategoriesData) > 0)
            { 			
				foreach ($appraisalCategoriesData as $ac){
					$appraisalquestionsform->pa_category_id->addMultiOption($ac['id'],utf8_encode($ac['category_name']));
				}
			}
			else
			{
				$msgarray['pa_category_id'] = 'Category names are not configured yet.';
				$this->view->configuremsg = 'notconfigurable';
			}
	 	$this->view->popConfigPermission = $popConfigPermission;
		$appraisalquestionsform->setAttrib('action',DOMAIN.'appraisalquestions/add');
		$this->view->form = $appraisalquestionsform; 
		$this->view->msgarray = $msgarray; 
		$this->view->ermsg = '';
			if($this->getRequest()->getPost()){
				 $result = $this->add($appraisalquestionsform);	
				 $this->view->msgarray = $result; 
			}  		
		
	}

    public function viewAction()
	{	
		$id = $this->getRequest()->getParam('id');
		$callval = $this->getRequest()->getParam('call');
		if($callval == 'ajaxcall')
			$this->_helper->layout->disableLayout();
		$objName = 'appraisalquestions';
		$appraisalquestionsform = new Default_Form_Appraisalquestions();
		$appraisalCategoryModel = new Default_Model_Appraisalcategory();
		$appraisalquestionsmodel = new Default_Model_Appraisalquestions();
		$appraisalquestionsform->removeElement("submit");
		$elements = $appraisalquestionsform->getElements();
		if(count($elements)>0)
		{
			foreach($elements as $key=>$element)
			{
				if(($key!="Cancel")&&($key!="Edit")&&($key!="Delete")&&($key!="Attachments")){
				$element->setAttrib("disabled", "disabled");
					}
        	}
        }
		try
		{
		    if($id)
			{
			    if(is_numeric($id) && $id>0)
				{
					$data = $appraisalquestionsmodel->getAppraisalQuestionbyID($id);
					if(!empty($data))
					{
						$data = $data[0]; 
						$appraisalCategoriesData = $appraisalCategoryModel->getAppraisalCategoryDatabyID($data['pa_category_id']);
						if(sizeof($appraisalCategoriesData) > 0)
						  $appraisalquestionsform->pa_category_id->addMultiOption($appraisalCategoriesData[0]['id'],utf8_encode($appraisalCategoriesData[0]['category_name']));
						$appraisalquestionsform->populate($data);
					}else
					{
					   $this->view->ermsg = 'norecord';
					}
                } 
                else
				{
				   $this->view->ermsg = 'norecord';
				}				
			}
            else
			{
			   $this->view->ermsg = 'norecord';
			} 			
		}
		catch(Exception $e)
		{
			   $this->view->ermsg = 'nodata';
		}
		$this->view->controllername = $objName;
		$this->view->id = $id;
		$this->view->form = $appraisalquestionsform;
		$this->render('form');	
	}
	
	
	public function editAction()
	{	
	    $auth = Zend_Auth::getInstance();
     	if($auth->hasIdentity()){
					$loginUserId = $auth->getStorage()->read()->id;
					$loginuserRole = $auth->getStorage()->read()->emprole;
					$loginuserGroup = $auth->getStorage()->read()->group_id;
		}
	 	
		$id = $this->getRequest()->getParam('id');
		$callval = $this->getRequest()->getParam('call');
		if($callval == 'ajaxcall')
			$this->_helper->layout->disableLayout();
		
		$appraisalquestionsform = new Default_Form_Appraisalquestions();
		$appraisalCategoryModel = new Default_Model_Appraisalcategory();
		$appraisalquestionsmodel = new Default_Model_Appraisalquestions();	
		$msgarray = array();
		$popConfigPermission = array();
		
		if(sapp_Global::_checkprivileges(APPRAISALQUESTIONS,$loginuserGroup,$loginuserRole,'add') == 'Yes'){
	 		array_push($popConfigPermission,'appraisalquestions');
	 	}
		$appraisalquestionsform->submit->setLabel('Update');
		
		try
        {		
			if($id)
			{
			    if(is_numeric($id) && $id>0)
				{
					$data = $appraisalquestionsmodel->getAppraisalQuestionbyID($id);
					if(!empty($data))
					{
						  $data = $data[0];
						$appraisalCategoriesData = $appraisalCategoryModel->getAppraisalCategorysData();
				 		if(sizeof($appraisalCategoriesData) > 0)
			            { 			
							foreach ($appraisalCategoriesData as $ac){
								$appraisalquestionsform->pa_category_id->addMultiOption($ac['id'],utf8_encode($ac['category_name']));
							}
						}  
						$appraisalquestionsform->populate($data);
						$appraisalquestionsform->setDefault('pa_category_id',$data['pa_category_id']);
						$appraisalquestionsform->setAttrib('action',DOMAIN.'appraisalquestions/edit/id/'.$id);
                        $this->view->data = $data;
					}else
					{
						$this->view->ermsg = 'norecord';
					}
				}
                else
				{
					$this->view->ermsg = 'norecord';
				}				
			}
			else
			{
				$this->view->ermsg = '';
			}
		}	
		catch(Exception $e)
		{
			   $this->view->ermsg = 'nodata';
		}	
		$this->view->form = $appraisalquestionsform;
		if($this->getRequest()->getPost()){
      		$result = $this->save($appraisalquestionsform);	
		    $this->view->msgarray = $result; 
		}
		$this->render('form');	
	}
	
	public function add($appraisalquestionsform)
	{
	  $auth = Zend_Auth::getInstance();
     	if($auth->hasIdentity()){
				 $loginUserId = $auth->getStorage()->read()->id;
		} 
	    $appraisalquestionsmodel = new Default_Model_Appraisalquestions();	
		$msgarray = array();
		$errorflag = 'true';
		$pa_category_id = $this->_request->getParam('pa_category_id');
		$question_arr = $this->_request->getParam('question');
		$quesArr = array_count_values($question_arr);
		$description_arr = $this->_request->getParam('description');
		if(!empty($question_arr))
		{
			for($i=0;$i<sizeof($question_arr);$i++)
			{
				$quesArr = array_count_values($question_arr);
				if($question_arr[$i] == '') {
					$msgarray['ques_name'][$i] = 'Please enter question.';
					$errorflag = 'false';
				} else if(!preg_match('/^[a-zA-Z0-9.\- ?]+$/', $question_arr[$i])) {
					$msgarray['ques_name'][$i] = 'Please enter valid question.';
					$errorflag = 'false';
				} else if($quesArr[$question_arr[$i]] > 1) {
					$msgarray['ques_name'][$i] = 'Question already exists for the department.';
					$errorflag = 'false';
				} else {
					if($pa_category_id!='')
					{
						$duplicateQuestionName = $appraisalquestionsmodel->checkDuplicateQuestionName($pa_category_id,$question_arr[$i]);
						if(!empty($duplicateQuestionName))
						{
							if($duplicateQuestionName[0]['count'] > 0)
							{
								$msgarray['ques_name'][$i] = 'Question already exists for the department.';
								$errorflag = 'false';
							}
						}
					}	
				}
			}
			$msgarray['requestsize'] = sizeof($question_arr);
		}	
		
		  if($appraisalquestionsform->isValid($this->_request->getPost()) && $errorflag == 'true'){
            try{
            $id = $this->_request->getParam('id');
            $pa_category_id = $this->_request->getParam('pa_category_id');
			$menumodel = new Default_Model_Menu();
			$actionflag = 1;
			$tableid  = ''; 
			$where = '';
			for($i=0;$i<sizeof($question_arr);$i++)
			{
			   $data = array('pa_category_id'=>$pa_category_id,
			                 'question'=>trim($question_arr[$i]), 
							 'description'=>($description_arr[$i]!=''?trim($description_arr[$i]):NULL),
							  'modifiedby'=>$loginUserId,
							  'modifieddate'=>gmdate("Y-m-d H:i:s"),
			   				  'createdby' => $loginUserId,
							  'createddate'=> gmdate("Y-m-d H:i:s"),
							  'isactive'=> 1
					);
				
				$Id = $appraisalquestionsmodel->SaveorUpdateAppraisalQuestionData($data, $where);
				$tableid = $Id; 	
				$menuidArr = $menumodel->getMenuObjID('/appraisalquestions');
				$menuID = $menuidArr[0]['id'];
				$result = sapp_Global::logManager($menuID,$actionflag,$loginUserId,$tableid);
			}
				
				$this->_helper->getHelper("FlashMessenger")->addMessage(array("success"=>"Question added successfully."));
				$this->_redirect('appraisalquestions');	
                  }
        catch(Exception $e)
          {
             $msgarray['pa_category_id'] = "Something went wrong, please try again.";
             return $msgarray;
          }
		}else
		{
			$messages = $appraisalquestionsform->getMessages();
			foreach ($messages as $key => $val)
				{
					foreach($val as $key2 => $val2)
					 {
						$msgarray[$key] = $val2;
						break;
					 }
				}
			return $msgarray;	
		}
	
	}
	
	
	public function save($appraisalquestionsform)
	{
	  $auth = Zend_Auth::getInstance();
     	if($auth->hasIdentity()){
				 $loginUserId = $auth->getStorage()->read()->id;
				 $loginuserRole = $auth->getStorage()->read()->emprole;
				 $loginuserGroup = $auth->getStorage()->read()->group_id;
		} 
	    $appraisalquestionsmodel = new Default_Model_Appraisalquestions();	
		$msgarray = array();
		  if($appraisalquestionsform->isValid($this->_request->getPost())){
            try{
            $id = $this->_request->getParam('id');
            $pa_category_id = $this->_request->getParam('pa_category_id');
            $question = trim($this->_request->getParam('question'));	
			$description = trim($this->_request->getParam('description'));
			$menumodel = new Default_Model_Menu();
			$actionflag = '';
			$tableid  = ''; 
			   $data = array('pa_category_id'=>$pa_category_id,
			                 'question'=>$question, 
							 'description'=>($description!=''?$description:NULL),			   
							 'module_flag'=>1, // 1 - Performance Appraisal
							 'modifiedby_role'=>$loginuserRole,
							 'modifiedby_group'=>$loginuserGroup,			   
							 'modifiedby'=>$loginUserId,
							 'modifieddate'=>gmdate("Y-m-d H:i:s")
					);
				if($id!=''){
					$where = array('id=?'=>$id);  
					$actionflag = 2;
				}
				else
				{
					$data['createdby_role'] = $loginuserRole;
					$data['createdby_group'] = $loginuserGroup;
					
					$data['createdby'] = $loginUserId;
					$data['createddate'] = gmdate("Y-m-d H:i:s");
					$data['isactive'] = 1;
					$where = '';
					$actionflag = 1;
				}
				$Id = $appraisalquestionsmodel->SaveorUpdateAppraisalQuestionData($data, $where);
				if($Id == 'update')
				{
				   $tableid = $id;
				   $this->_helper->getHelper("FlashMessenger")->addMessage(array("success"=>"Question updated successfully."));
				}   
				else
				{
				   $tableid = $Id; 	
					$this->_helper->getHelper("FlashMessenger")->addMessage(array("success"=>"Question added successfully."));					   
				}   
				$menuidArr = $menumodel->getMenuObjID('/appraisalquestions');
				$menuID = $menuidArr[0]['id'];
				$result = sapp_Global::logManager($menuID,$actionflag,$loginUserId,$tableid);
				$this->_redirect('appraisalquestions');	
                  }
        catch(Exception $e)
          {
             $msgarray['pa_category_id'] = "Something went wrong, please try again.";
             return $msgarray;
          }
		}else
		{
			$messages = $appraisalquestionsform->getMessages();
			foreach ($messages as $key => $val)
				{
					foreach($val as $key2 => $val2)
					 {
						$msgarray[$key] = $val2;
						break;
					 }
				}
			return $msgarray;	
		}
	
	}
	
	public function deleteAction()
	{
	     $auth = Zend_Auth::getInstance();
     		if($auth->hasIdentity()){
					$loginUserId = $auth->getStorage()->read()->id;
					$loginuserRole = $auth->getStorage()->read()->emprole;
				 	$loginuserGroup = $auth->getStorage()->read()->group_id;
				}
		 $id = $this->_request->getParam('objid');
		 $messages['message'] = '';
		 $messages['msgtype'] = '';
		 $count = 0;
		 $actionflag = 3;
		    if($id)
			{
				$appraisalquestionsmodel = new Default_Model_Appraisalquestions();
				$menumodel = new Default_Model_Menu();
				$appraisalquestionsdata = $appraisalquestionsmodel->getAppraisalQuestionbyID($id);
				
				if($appraisalquestionsdata[0]['isused'] == 0)	
				{
					  $data = array('isactive'=>0,'modifieddate'=>gmdate("Y-m-d H:i:s"),'modifiedby_role'=>$loginuserRole,
									 'modifiedby_group'=>$loginuserGroup,'modifiedby'=>$loginUserId);
					  $where = array('id=?'=>$id);
					  $Id = $appraisalquestionsmodel->SaveorUpdateAppraisalQuestionData($data, $where);
					    if($Id == 'update')
						{
						   $menuidArr = $menumodel->getMenuObjID('/appraisalquestions');
						   $menuID = $menuidArr[0]['id'];
						   $result = sapp_Global::logManager($menuID,$actionflag,$loginUserId,$id);
		                   $configmail = sapp_Global::send_configuration_mail('Question',$appraisalquestionsdata[0]['question']);				   
						   $messages['message'] = 'Question deleted successfully.';
						   $messages['msgtype'] = 'success';
						}   
						else
						{
		                   $messages['message'] = 'Question cannot be deleted.';
		                   $messages['msgtype'] = 'error';
		                }
				  }else
				  {
				  	   $messages['message'] = 'Question cannot be deleted as its using in appraisal process.';
	                   $messages['msgtype'] = 'error';
				  } 				   
			}
			else
			{ 
			 $messages['message'] = 'Question cannot be deleted.';
			 $messages['msgtype'] = 'error';
			}
			$this->_helper->json($messages);
		
	}
}

