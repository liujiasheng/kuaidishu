<?php

//factory and singleton
interface IUser{
    function getName();
}

class User implements IUser{

    private static $_instance;
    private $_timestamp;

    /**
     * @return User
     */
    public static function GetUser(){
        if(User::$_instance){
            return User::$_instance;
        }
        else{
            User::$_instance = new User();
            return User::$_instance;
        }
    }

    private function __construct( ){
        $this->_timestamp = new DateTime();
    }
    public function getName(){
        echo $this->_timestamp->getTimestamp()."\n";
        echo "Jack\n";
    }
}

//observer
interface IObserver{
    function onChanged( $sender, $args );
}
interface IObservable{
    function addObserver( $observer );
}
class UserList implements IObservable{
    /**
     * @var UserListLogger[]
     */
    private $_observers = array();
    private $_logo;

    public function __construct(){
        $this->_logo = (new DateTime())->getTimestamp();
    }
    public function addObserver( $observer ){
        $this->_observers[] = $observer;
    }
    public function addCustomer( $name ){
        foreach( $this->_observers as $obs ){
            $obs->onChanged( $this, $name);
        }
    }
    public function getLogo(){
        return $this->_logo;
    }
}
class UserListLogger implements IObserver{
    private $_title;
    public function __construct(){
        $this->_title = rand(1,10000);
    }
    public function getTitle(){
        return $this->_title;
    }
    public function onChanged( $sender, $args){
        echo $sender->getLogo().'::';
        echo $this->_title.'::';
        echo $args ." added to UserList\n";
    }
}

//命令链
interface ICommand{
    function onCommand( $name, $args );
}
class CommandChain{
    private $_commands = array();
    public function addCommand( $cmd ){
        $this->_commands[] = $cmd;
    }
    public function runCommand( $name , $args ){
        foreach($this->_commands as $cmd){
            if( $cmd->onCommand( $name, $args ) ) return;
        }
    }
}
class UserCommand implements ICommand{
    public function onCommand( $name, $args ){
        if($name == 'user'){
            echo "execute user command\n";
            return true;
        }
        return false;
    }
}
class MailCommand implements ICommand{
    public function onCommand( $name, $args ){
        if($name == 'mail'){
            echo "execute mail command\n";
            return true;
        }
        return false;
    }
}

//strategy
interface IStrategy{
    function sorter( &$arr );
}
class RandomStrategy implements IStrategy{
    public function sorter( &$arr ){
        shuffle($arr);
        return $arr;
    }
}
class DescStrategy implements IStrategy{
    public function sorter( &$arr ){
        arsort($arr);
        return $arr;
    }
}

class WorkerList{
    private $_list = array();
    public function add( $name ){
        $this->_list[] = $name;
    }

    /**
     * @param $sorter IStrategy
     * @return array
     */
    public function runSort( $sorter ){
        return $sorter->sorter($this->_list);
    }
    public function display(){
        print_r($this->_list);
    }
}

//main
//for($i = 0; $i<100; $i++){
//    User::GetUser()->getName();
//    sleep(1);
//}

//$list = new UserList();
//$list->addObserver( new UserListLogger() );
//$list->addObserver( new UserListLogger() );
//$list->addCustomer( 'Jack' );
//$list->addCustomer( 'Lucy' );
//$list->addCustomer( 'Tom' );

//$chain = new CommandChain();
//$chain->addCommand( new UserCommand() );
//$chain->addCommand( new MailCommand() );
//$chain->runCommand('user',null);
//$chain->runCommand('mail',null);
//$chain->runCommand('mail',null);

//$list = new WorkerList();
//$list->add('Mary');
//$list->add('Tom');
//$list->add('Tony');
//$list->add('Garry');
//$list->display();
//print_r($list->runSort(new RandomStrategy()));
//$list->display();
//print_r($list->runSort(new DescStrategy()));
//$list->display();

?>