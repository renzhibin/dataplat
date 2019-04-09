<?php
class MailResetCommand extends Command {
    public  $objVisual;
    public  $objFackcube;
    public  $objComm;
    public  $objMail;
    // public  $objVisual;
    // public  $objVisual;
    // public  $objVisual;
    function __construct(){
        $this->objVisual=new VisualManager();
        $this->objFackcube=new FackcubeManager();
        //$this->objReport=new ReportManager();
        $this->objComm=new CommonManager();
        $this->objMail= new TimeMailManager();
    }

    /**
     * @return bool|void
     */
    function main(){
        //还原邮件状态
        $this->objMail->resetStatau();
         
    }

}
