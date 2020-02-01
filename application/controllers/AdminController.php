<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AdminController extends CI_Controller {
    public function _construct(){
        parent::_construct();
        //$this->load-library('vendor/php-excel-reader/excel_reader2.php');
        //$this->load-library('vendor/SpreadsheetReader.php');
        //$this->load->library('PHPExcel/IOFactory.php');
        //$this->load->library('Excel');

        require_once APPPATH.'third_party/PHPExcel.php';
        $this->excel = new PHPExcel(); 
    }
	public function index(){
	}
    function loadAdminPage($page=""){
        $page_data['rolelist'] = $this->db->get_where('t_role_master',array('Role_Status'=>'Y'))->result_array();
        $page_data['userList'] = $this->db->get('t_User_details')->result_array();
        $this->load->view('admin/administrator/'.$page,$page_data);
    }
    function addUser(){
        $page_data['message']="";
        $page_data['messagefail']="";
        $data['CID']=$this->input->post('CID');
        $data['Full_Name']=$this->input->post('Full_Name');
        $data['Contact_Number']=$this->input->post('Contact_Numer');
        $data['User_Id']=$this->input->post('User_Id');
        $data['Role_Id']=$this->input->post('Role_Id');
        $data['Password']=$this->input->post('Password');
        $this->CommonModel->do_insert('t_user_details', $data); 
        if($this->db->affected_rows()>0){
            $page_data['message']="User details for ".$this->input->post('Full_Name')." has beed added with user name:<b>".$this->input->post('User_Id')."</b> and password:<b>".$this->input->post('Password')."</b>. Thank you for using our system";
        }
        else{
            $page_data['messagefail']='User is not albe to submit. Please try again';
        }
        $this->load->view('admin/acknowledgement', $page_data); 
    }
	function loadPage($page="",$id=""){
        $page_data['userDetils'] =$this->CommonModel->getuserDetails($id);
		$this->load->view('common/'.$page,$page_data);
	}
    function updateUser(){
        $page_data['messagefail']="";
        //die($this->input->post('Contact_Numer'));
        $data['CID']=$this->input->post('CID');
        $data['Full_Name']=$this->input->post('Full_Name');
        $data['Contact_Numer']=$this->input->post('Contact_Number');
        $data['User_Id']=$this->input->post('User_Id');
        $data['Password']=$this->input->post('Password');
        
        $this->db->where('Id',  $this->input->post('userId'));
        $this->db->update('t_user_details`', $data);
        $page_data['message']="Details are updated. Thank you for using our system";
        $this->load->view('admin/acknowledgement', $page_data); 
    }
    //function to delete users
	function updatestaus($iserId="",$stus=""){
        if($stus=="Yes" || $stus=="Y"){
		  $data['User_Status']='N';
        }
        else{
           $data['User_Status']='Y'; 
        }
        $this->db->where('Id',  $iserId);
        $this->db->update('t_user_details`', $data);
        $page_data['rolelist'] = $this->db->get_where('t_role_master',array('Role_Status'=>'Y'))->result_array();
        $page_data['userList'] = $this->db->get('t_User_details')->result_array();
        $this->load->view('admin/administrator/ListUser',$page_data);
	}
    //function to update role for the user by admin
	function Updaterole($page=""){
        $data['Role_Id']=$this->input->post('User_List');
        $this->db->where('Id',  $this->input->post('deleteId'));
        $this->db->update('t_user_details`', $data);
        $page_data['rolelist'] = $this->db->get_where('t_role_master',array('Role_Status'=>'Y'))->result_array();
        $page_data['userList'] = $this->db->get_where('t_User_details',array('User_Status'=>'Y'))->result_array();
		$this->load->view('admin/administrator/ListUser',$page_data);
	}
   
    function loadimportPage($page=""){
        $page_data['rolelist'] ="";
        $this->load->view('data/'.$page,$page_data);
    }


    function insertexcelData($type=""){     
        require_once APPPATH.'third_party/PHPExcel.php';
        $this->excel = new PHPExcel();  
        $file_info = pathinfo($_FILES["msubscriber"]["name"]);
        $file_directory = "uploads/attachments/".date("Y").'/'.date("M");
        if(!is_dir($file_directory)){
            mkdir($file_directory,0777,TRUE);
        }
        $new_file_name = $_FILES["msubscriber"]["name"];
        move_uploaded_file($_FILES["msubscriber"]["tmp_name"], $file_directory . $new_file_name);
        $file_type  = PHPExcel_IOFactory::identify($file_directory . $new_file_name);
        $objReader  = PHPExcel_IOFactory::createReader($file_type);
        $objPHPExcel = $objReader->load($file_directory . $new_file_name);
        $sheet_data = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
        $file_type  = PHPExcel_IOFactory::identify($file_directory . $new_file_name);
        $objReader  = PHPExcel_IOFactory::createReader($file_type);
        $objPHPExcel = $objReader->load($file_directory . $new_file_name);
        $sheet_data = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
        $Prepaid_Active_main=0;
        $Prepaid_Passive_Total=0;
        $Prepaid_Total_count=0;
        $Post_Active_count=0;
        $Post_Passive_count=0;
        $Post_Total_count=0;
        $Total_Active_total=0;
        $Total_Registered_count=0;
        $rowcount=0;
        foreach($sheet_data as $i=> $data) {
            if($i>2){
                $Prepaid_Active_main=$Prepaid_Active_main+$data['B'];
                $Prepaid_Passive_Total=$Prepaid_Passive_Total+$data['C'];
                $Prepaid_Total_count=$Prepaid_Total_count+$data['D'];
                $Post_Active_count=$Post_Active_count+$data['E'];
                $Post_Passive_count=$Post_Passive_count+$data['F'];
                $Post_Total_count=$Post_Total_count+$data['G'];
                $Total_Active_total=$Total_Active_total+$data['H']; 
                $Total_Registered_count=$Total_Registered_count+$data['J'];                    
                $rowcount++;

                $result = array(
                    'Year' => $this->input->post('Year'),
                    'Month' => $this->input->post('month'),
                    'File_Date' => $data['A'],
                    'Pre_Active' => $data['B'],
                    'Pre_Grace' => $data['C'],
                    'Pre_Total_Registered' => $data['D'],
                    'Post_Active' => $data['E'],
                    'Post_Grace' => $data['F'],
                    'Post_Total_Registered' => $data['G'],
                    'Total_Active' => $data['H'],
                    'Total_Grace' => $data['I'],
                    'User_Id' => $this->session->userdata('User_table_id'),
                    'Added_Date' => date("Y-m-d"),                    
                );
                $this->CommonModel->do_insert('t_subscriber_bmobile_excel',$result);
            }
        }
        $Prepaid_Active=$Prepaid_Active_main/ $rowcount;
        $Prepaid_Passive=$Prepaid_Passive_Total/ $rowcount;
        $Prepaid_Total=$Prepaid_Total_count/$rowcount;
        $Post_Active=$Post_Active_count/$rowcount;
        $Post_Passive=$Post_Passive_count/$rowcount;
        $Post_Total=$Post_Total_count/$rowcount;
        $Total_Active=$Total_Active_total/$rowcount;
        $Total_Registered=$Total_Registered_count/$rowcount;

        $file_name = $_FILES["lhrattachment"]["name"];
        /*$file_directory = "uploads/attachments/".date("Y").'/'.date("M").'/HLR';
        if(!is_dir($file_directory)){
            mkdir($file_directory,0777,TRUE);
        }*/
        move_uploaded_file($_FILES["lhrattachment"]["tmp_name"], $file_directory . $new_file_name);
        $result = array(
            'Year' => $this->input->post('Year'),
            'Month' => $this->input->post('month'),
            'Prepaid_Active' => $Prepaid_Active,
            'Prepaid_Passive' => $Prepaid_Passive,
            'Prepaid_Total' => $Prepaid_Total,
            'Post_Active' => $Post_Active,
            'Post_Passive' => $Post_Passive,
            'Post_Total' => $Post_Total,
            'Total_Active' => $Total_Active,
            'Total_Registered' => $Total_Registered,            
            'HLR' => $this->input->post('lhr'),
            'HLR_Attachment' => $this->input->post('lhrattachment'),            
            'User_Id' => $file_name,
            'Added_Date' => date("Y-m-d"),                 
        );
        $this->CommonModel->do_insert('t_subscriber_bmobile_main',$result);
        
    }

    function insertflexcelData($type=""){   
  
        require_once APPPATH.'third_party/PHPExcel.php';
        $this->excel = new PHPExcel();  
        $file_info = pathinfo($_FILES["fsubscriber"]["name"]);
        $file_directory = "uploads/attachments/".date("Y").'/'.date("M");
        if(!is_dir($file_directory)){
            mkdir($file_directory,0777,TRUE);
        }
        $new_file_name = $_FILES["fsubscriber"]["name"];
        move_uploaded_file($_FILES["fsubscriber"]["tmp_name"], $file_directory . $new_file_name);
        $file_type  = PHPExcel_IOFactory::identify($file_directory . $new_file_name);
        $objReader  = PHPExcel_IOFactory::createReader($file_type);
        $objPHPExcel = $objReader->load($file_directory . $new_file_name); 
        $sheet_data = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);    
        $Grand_Total=0;
        $rowcount=0;
        foreach($sheet_data as $i=> $data) {
            if($i>4 && $i<25){
            $result = array(
                    'Year' => $this->input->post('Year'),
                    'Month' => $this->input->post('month'),
                    'Dzongkhag' => $data['B'],
                    'Jan' => $data['C'],
                    'Feb' => $data['D'],
                    /*'March' => $data['E'],
                    'Aprl' => $data['F'],
                    'May' => $data['G'],
                    'Jun' => $data['H'],
                    'July' => $data['I'],
                    'Aug' => $data['J'],
                    'Sep' => $data['K'],
                    'Oct' => $data['L'],
                    'Nov' => $data['M'],
                    'Dec' => $data['N'],*/
                    'User_Id' => $this->session->userdata('User_table_id'),
                    'Added_On' => date("Y-m-d"),                    
                );
                $this->CommonModel->do_insert('t_subscriber_fl_excel',$result);


            }
            if($i==25){ 
              $Grand_Total= $data['C'];     
        }
           $result = array(
                    'Year' => $this->input->post('Year'),
                    'Month' => $this->input->post('month'),
                    'Subscriber' => $Grand_Total,
                    'User_Id' => $this->session->userdata('User_table_id'),
                    'Added_On' => date("Y-m-d"),
                );
            $this->CommonModel->do_insert('t_subscriber_fixed_line_main',$result); 
        }
        $page_data['messagefail']="";
        $page_data['message']="Details are updated.Thank you for using our system";
        $this->load->view('admin/acknowledgement', $page_data); 
        
    }





    function searchDetails(){
       $page_data['result_list'] =$this->CommonModel->getappdetailsforreport($this->input->post('userid'));
       $this->load->view('admin/searchresult',$page_data); 
    }
    function searchDetailsgenerate($id=""){
        $page_data['application_detail'] =$this->CommonModel->getApplicaionDetails('finalReport',$id);
        $this->load->view('admin/finalreport',$page_data); 
    }
}

