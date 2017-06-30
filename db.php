<?php 
	class Db
	{

		/**************在此之下填写相应数据***************/
		private $host = '配置主机名';
		private $username = '登录名';
		private $passwd ='密码';
		private $dbname ='数据库名称';
		private $port='3306';
		private $sql = '';
		private $mysqli = null;
		/**************在此之上填写相应数据***************/
		
		/**
		 * [__construct 构造函数]
		 * @param [type] $arr [host=>host,username=>username,passwd=>passwd,dbname=>dbname,prot=>port]
		 */
		public function __construct($arr=null)
		{
			if(isset($arr['host'])){
				$this->host=$arr['host'];
			}
			if(isset($arr['username'])){
				$this->username=$arr['username'];
			}
			if(isset($arr['passwd'])){
				$this->passwd=$arr['passwd'];
			}
			if(isset($arr['dbname'])){
				$this->dbname=$arr['dbname'];
			}
			if(isset($arr['port'])){
				$this->port=$arr['port'];
			}
			$this->mysqli=new mysqli($this->host,$this->username,$this->passwd,$this->dbname,$this->port);
			if($this->mysqli->connect_error){
				$this->error='连接错误('.$this->mysqli->connect_errno.')：'.$this->$mysqli->connect_error;
			}
		}

		/**
		 * [setCharset 设置字符集]
		 * @param [string] $character [字符名称]
		 */
		public function setCharset($character){
			if(!$this->mysqli->set_charset($character)){
				 return false;
			}else{
				return true;
			}	
		}
		/**
		 * [insert 插入一条数据]
		 * @param  [string] $table [表名]
		 * @param  [array] $data  [由字段名当键，属性当键值的一维数组]
		 * @return [type]        [返回false或者插入数据的id]
		 */
		public function insert($table,$data){
			$this->sql = '';
			$this->sql.= "INSERT INTO `$table`";
			$this->sql.="(`".implode("`,`",array_keys($data))."`) "; 
			$this->sql.="VALUES";
			$this->sql.="('".implode("','",$data)."')";
			$res = $this->mysqli->query($this->sql);
			if($res&&$this->mysqli->affected_rows){
				return $this->mysqli->insert_id;
			}else{
				return false;
			}
		}

		/**
		 * [update  更新数据库]
		 * @param  [string] $table [表名]
		 * @param  [array] $data  [更新数据，由字段名当键，属性当键值的一维数组]
		 * @param  [string] $where [条件，‘字段名’=‘字段属性’]
		 * @return [type]        [更新成功返回影响的行数，更新失败返回false]
		 */
		public function update($table,$data,$where){
			$this->sql='';
			$this->sql.= 'UPDATE `'.$table.'` SET ';
			foreach($data as $key => $value){
				$this->sql.="`{$key}`='{$value}',";
			}
			$this->sql=rtrim($this->sql,',');
			$this->sql.= " WHERE $where";
			$res = $this->mysqli->query($this->sql);
			if($res&&$this->mysqli->affected_rows){
				return $this->mysqli->affected_rows;
			}else{
				return false;
			}
		}

		/**
		 * [del 删除数据]
		 * @param  [string] $table [表名]
		 * @param  [string] $where [条件，‘字段名’=‘字段属性’]
		 * @return [type]        [成功返回影响的行数，失败返回false]
		 */
		public function del($table,$where){
			$this->sql='';
			$this->sql="DELETE FROM `{$table}` WHERE {$where}";
			$res = $this->mysqli->query($this->sql);
			if($res&&$this->mysqli->affected_rows){
				return $this->mysqli->affected_rows;
			}else{
				return false;
			}
		}

		/**
		 * [select 获取数据]
		 * @param  [string] $table [表名]
		 * @param  [array] $data  [需要查询字段的索引数组]
		 * @param  [string] $where [条件，‘字段名’=‘字段属性’]
		 * @return [type]        [成功返回二维数组，失败返回false]
		 */
		public function select($table,$data,$where=''){
			$this->sql='';
			$this->sql='SELECT ';
			$this->sql.=implode(",",$data);
			$this->sql.= " FROM `$table` ";
			if($where){
				$this->sql.= " WHERE $where";
			}
			$res = $this->mysqli->query($this->sql);
			$temp = array();
			if($res&&$res->num_rows>0){
				while ($row = $res->fetch_assoc()){
					$temp[]=$row;
				}
				return $temp;
			}else{
				return false;
			}
		}
		public function __destruct(){
			$this->mysqli->close();
		}
	}



