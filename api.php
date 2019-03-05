<?php
class worms_battle {
    var $db;
    var $arr_setting;


    /******************************************/
    function __construct() {
        $this->read_setting();
        $this->db = new mysqli(
                              $this->setting('dbhost'), 
                              $this->setting('dbuser'), 
                              $this->setting('dbpass'), 
                              $this->setting('dbname')
                              );
        /* $this->create_update_db(); */
    }
    /******************************************/
    function read_setting() {
        if (file_exists('config.php')) {
            include_once('config.php');
            if (is_array($setting)) {
                $this->arr_setting = $setting;
            }
        }
    }
    /******************************************/
    function __destruct() {
        @$this->db->close();
    }
    /******************************************/
    function setting($param, $value = null) {
        if (!is_null($value)) {
            $this->arr_setting[$param] = $value;
        }
        return $this->arr_setting[$param];
      }

    /******************************************/
    function sql_db() {
        return $this->db;
    }
    /******************************************/
    function sql_query($query) {
        if (trim($query) != '') {
            $result = $this->sql_db()->query($query);
            if ($this->sql_db()->errno > 0) {
                echo $query.'<br>'.$this->sql_db()->error;
            }
        }
        return $result;
    }
    /******************************************/
    function sql_all_result($query) {
        $result = $this->sql_query($query);
        $arr_result = array();
        if ($result) {
            while ($data = $result->fetch_array(MYSQLI_ASSOC)) {
                $arr_result[] = $data;
            };
        }
        return $arr_result;
    }
    /******************************************/
    function res($text) {
        return $this->db->real_escape_string($text);
    }
    /******************************************/
    function create_user ($login, $pass, $name) {
        $login = $this->res($login);
        $pass = $this->res($pass);
        $name = $this->res($name);
        $query = 'INSERT INTO user (ID, login, pass, name) VALUES (null, "'.$login.'", "'.$pass.'" , "'.$name.'");';
        $this->sql_query($query);
        $id = $this->sql_db()->insert_id;
        return $id;
    }
    /******************************************/
    function get_user_id ($login, $pass) {

        return $id;
    }
    /******************************************/
    function delete_user ($id) {
        
    }
    /******************************************/
    function get_user_params ($id, $need_user = false) {
        if (is_numeric($id)) {
            if ($need_user) {
                $query = 'SELECT * FROM user WHERE ID = "'.$id.'"';
                $arrParams['user'] = $this->sql_all_result($query);
            };
            $query = 'SELECT u.ID as ID, up.ID as ID_param, up.code as code, up.title as title, u.val as val FROM user_param AS u LEFT JOIN var_user_param as up ON u.ID_param= up.ID WHERE u.ID_user = "'.$id.'"';
            $arrParams['param'] = $this->sql_all_result($query);
            $arrParams['paramAssoc'] = array();
            if (is_array($arrParams['param'])) {
                foreach ($arrParams['param'] as $key => $val) {
                    $arrParams['paramAssoc'][$val['code']] = $val['val'];
                }
            }
            return $arrParams;
        }
        return false;
    }
    /******************************************/
    function set_user_params ($id, $arrParams) {
        if ((is_array($arrParams)) && (is_numeric($id))) {
            if (count($arrParams['user']) > 0) {
                $user = $arrParams['user'][0];
                $avalableFields = array('name'=>'' , 'login'=>'MD5', 'pass'=>'MD5');
                $set = '';
                $setand = '';
                foreach ($avalableFields as $key => $value) {
                    if (isset($user[$key])) {
                        if ($value != '') {
                            $set .= $setand. $key .' = '.$value .'("'.$this->res($user[$key]).'")';
                        } else {
                            $set .= $setand. $key .' = "'.$this->res($user[$key]).'"';
                        }
                        $setand = ', ';
                    }
                }
                $query = 'UPDATE user SET '.$set.' WHERE ID = '.$id;
                $this->sql_query($query);
            }
            if (count($arrParams['param']) > 0) {
                foreach ($arrParams['param'] as $arrCurParam) {
                    if (is_array($arrCurParam)) {
                        foreach ($arrCurParam as $key=>$val) {
                            if (is_array($val)) {
                                if (is_numeric($val['ID_param'])) {
                                    $val['val'] = $this->res($val['val']);
                                    $query = 'INSERT INTO user_param (ID, ID_user, ID_param, val) VALUES  ('.
                                                    'null, '.
                                                    $id.', '.
                                                    $val['ID_param'].','.
                                                    '"'.$val['val'].'") '.
                                                    'ON DUPLICATE KEY UPDATE val = "'.$val['val'].'";';
                                    $this->sql_query($query);
                                }
                            }
                        }
                    }
                }
            }

        };
        return $arrParams;
    }
    /******************************************/
    function get_all_active_users() {
        return $arrID;
    }
    /******************************************/
    function get_neer_users($posX, $posY) {

        return $arrID;
    }
    /******************************************/
    function set_object($posX, $posY, $type, $arrParams) {
        return $id;
    }
    /******************************************/
    function get_neer_objects($posX, $posY) {
        return $arrID;
    }
    /******************************************/
    function get_all_active_objects() {
        return $arrID;
    }
    /******************************************/
    function get_object_params ($id) {
        return $arrParams;
    }
    /******************************************/
    function get_game_field ($posX, $posY) {
        $playersIDs = $this->get_neer_users($posX, $posY);
        $objectIDs = $this->get_neer_object($posX, $posY);
        $gamedata = array();
        foreach ($playersIDs as $key=>$id ) {
            $gamedata['players'][] = $this->get_user_params($id);
        };
        foreach ($objectIDs as $key=>$id ) {
            $gamedata['objects'][] = $this->get_object_params($id);
        };
        return $gamedata;
    }
    /******************************************/
    function microtime_float() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }    
    /******************************************/
    function is_busy() {
        $lasttime =  file_get_contents('lastupdate.txt');
        $curtime = $this->microtime_float();
        if ($lasttime != '') {
            if ($curtime - $lasttime < $this->setting('takt')) {
                return true;                
            }
        }
        file_put_contents('lastupdate.txt', $curtime);
        return false;
    }
    /******************************************/
    function game_takt() {
        if (!is_busy) {
            $playersIDs = $this->get_all_active_users($posX, $posY);
            $objectIDs = $this->get_all_active_objects($posX, $posY);
            $gamedata = array();
            foreach ($playersIDs as $key=>$id ) {
                $gamedata['players'][] = $this->get_user_params($id);
            };
            foreach ($objectIDs as $key=>$id ) {
                $gamedata['objects'][] = $this->get_object_params($id);
            };
        };
    }
    /******************************************/
}
?>