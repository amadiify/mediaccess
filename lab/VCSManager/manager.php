<?php

use Moorexa\DB;
use Moorexa\Hash;

class VCSManager
{
    // @var $connectionIdentifier
    public $connectionIdentifier = 'mysql';

    // @var $db : databse instance
    private $db = null;

    // authorization 
    private $authorization = null;

    // authenticated user
    private $authUserid = null;

    // release note
    private $releaseNote = null;

    // constructor method.
    public function __construct()
    {
        if (!is_null($this->connectionIdentifier))
        {
            $this->db = DB::apply($this->connectionIdentifier);
        }
        else
        {
            $this->db = DB::serve();
        }
    }

    /**
     * @method Auth 
     * @param $username : String
     * @param $password : String
     * 
     * Authenticate User
     */
    public function auth($username, $password, &$error = [])
    {
        // check if username exists
        $user = $this->db->table('VcsUsers')->get('username = ?', $username);
       
        if ($user->rows > 0)
        {
            $this->authUserid = $user->VcsUsersid;

            // verify password hash
            if (Hash::verify($password, $user->password))
            {
                // get authorization
                $role = $this->db->table('VcsRoles')->get('VcsRolesid', $user->VcsRolesid);

                if ($role->rows > 0)
                {
                    $this->authorization = $role->Permission;
                }
                else
                {
                    $error[] = "User has no permission.";
                }

                return true;
            }

            $error[] = "Incorrect Password!";
        }
        else
        {
            $error[] = "Invalid Username";
        }

        return false;
    }

    // manage request authorization
    public function __call($request, $arguments)
    {
        if ($this->authorization !== null)
        {
            $auth = array_flip(explode(",", $this->authorization));

            if (isset($auth[$request]))
            {
                // good!
                $meth = '_'.$request;
                // call method here
                $this->{$meth}($arguments);
            }
            else
            {
                $this->warning("You are not authorized to make a '$request' request.");
            }
        }
        else
        {
            $this->warning("Authorization failed! '$request' request could not continue.");
        }

        return false;
    }

    /**
     * @method Warning 
     * @param $message : String or Array
     * 
     * Show User Warning
     */
    public function warning($message)
    {
        static $showing;

        if (is_null($showing))
        {
            $showing = true;
            
            if (is_array($message))
            {
                $msg = null;
                foreach ($message as $i => $m)
                {
                    $msg = $m;
                    break;
                }
                echo json_encode(['warning' => $msg]);
            }
            else
            {
                echo json_encode(['warning' => $message]);
            }
        }
    }

    /**
     * @method watchVersionManager 
     * @param void
     * 
     * Watch Version Remote Access.
     */

    public function watchVersionManager()
    {
        $remote = include_once (HOME . 'lab/VCSManager/remote.php');
        $key = $remote['sharedKey'];

        if ($key == $_GET['sharedKey'])
        {
            $username = isset($_GET['username']) ? $_GET['username'] : null;
            $password = isset($_GET['password']) ? $_GET['password'] : null;

            if (isset($_GET['note']))
            {
                $len = trim(strlen($_GET['note']));

                if ($len > 2)
                {
                    $this->releaseNote = $_GET['note'];
                }
            }

            if (!is_null($username) && !is_null($password))
            {
                // continue
                $auth = $this->auth($username, $password, $error);
                
                if ($auth !== false)
                {
                    $method = $_GET['vcsmethod'];
                    $this->{$method}();
                }
                else
                {
                    $this->warning($error);
                }
                
                $vcs = null;

            }
            else
            {
                $this->warning('Invalid Username or Password supplied!');
            }
        }
        else
        {
            $this->warning('Invalid Shared Key!');
        }

		die();
    }

    // PRIVATE METHODS HERE.. 

    // push version
    private function _push()
    {
        if (isset($_FILES['pushFile']))
        {
            $file = $_FILES['pushFile'];

            if ($file['error'] == 0)
            {
                // get name
                $name = rtrim($file['name'], '.zip');
                // extract content
                $folder = PATH_TO_VERSION . $name.'/';
                if (!is_dir($folder))
                {
                    @mkdir($folder);
                }

                if (is_dir($folder))
                {
                    if (move_uploaded_file($file['tmp_name'], $folder . $file['name']))
                    {
                        $zip = new ZipArchive();
                        
                        if ($zip->open($folder . $file['name']))
                        {
                            $zip->extractTo($folder);
                        }

                        $zip->close();
                        @unlink($folder . $file['name']);

                        if (is_null($this->releaseNote))
                        {
                            $this->releaseNote = 'Pushed his/her current release on version '.$name;
                        }

                        $this->recordTransaction('push', $this->releaseNote);

                        echo json_encode(['success' => 'Pushed']);
                    }
                    else
                    {
                        $this->warning('Unable to read zip file.');
                    }
                }
                else
                {
                    $this->warning('Couldn\'t create or read remote directory : '.$folder);
                }
            }
            else
            {
                $this->warning('File upload failed.');
            }
        }
        else
        {
            $this->warning("PUSH File not sent!");
        }
    }

    // publish version to production
    private function _publish()
    {
        $mode = strip_tags($_GET['mode']);
        $version = strip_tags($_GET['version']);

        $ignore = ['-prod', '-dev', '-development', '-production', '-live', '-remote'];

        if (strlen($version) < 1 || in_array($version, $ignore))
        {
            $versions = PATH_TO_VERSION;
            
            $all = glob($versions. '*');
            $vers = [];
            foreach ($all as $i => $o)
            {
                if (is_dir($o) && basename($o) != 'Rollbacks')
                {
                    $dir = stat($o);
                    $stamp = $dir['ctime'];
                    $vers[$stamp] = basename($o);
                }
            }

            $max = max(array_keys($vers));
            $version = $vers[$max];
        }

        $path = PATH_TO_VERSION . $version;

        if (is_dir($path))
        {
            $xml = simplexml_load_file(HOME . 'config.xml');
            $prod = (array) $xml->versioning->production;
            $dev = (array) $xml->versioning->development;
            $xml->versioning->production = $version;
            file_put_contents(HOME . 'config.xml', $xml->saveXML());

            $_version = json_encode(['production' => $prod[0], 'development' => $dev[0]]);
            $filename = preg_replace('/(-|:|\s*)/', '', date('Y-m-d g:i:s'));
            $filename .= '_rollback.json';

            file_put_contents(PATH_TO_VERSION . 'Rollbacks/' . $filename, $_version);

            if (is_null($this->releaseNote))
            {
                $this->releaseNote = 'Published version v'.$version;
            }

            $this->recordTransaction('publish', $this->releaseNote);

            echo json_encode(['success' => 'Published', 'version' => $version]);
        }
        else
        {
            $this->warning('Version '.$version.' doesn\'t exists.');
        }
    }

    // rollback transaction
    private function _rollback()
    {
        $mode = strip_tags($_GET['mode']);
        $version = strip_tags($_GET['version']);
        $prod = true;

        if ($version == null || strlen(trim($version)) == 0)
        {
            $rollbacks = PATH_TO_VERSION . 'Rollbacks/';
            $all = glob($rollbacks. '*');
            $vers = [];
            foreach ($all as $i => $o)
            {
                if (is_file($o))
                {
                    $file = stat($o);
                    $stamp = $file['ctime'];
                    $vers[$stamp] = basename($o);
                }
            }

            $max = max(array_keys($vers));
            $version = $vers[$max];
        }
        else
        {
            if (strpos($version, '_') === false)
            {
                $version .= '_rollback.json';
            }
        }

        $dir = PATH_TO_VERSION . 'Rollbacks/';
        if (file_exists($dir . $version))
        {
            $obj = json_decode(trim(file_get_contents($dir . $version)));

            if (is_object($obj))
            {
                $xml = simplexml_load_file(HOME . 'config.xml');

                $_prod = (array) $xml->versioning->production;
                $_prod = $_prod[0];
                $_dev = (array) $xml->versioning->development;
                $_dev = $_dev[0];

                $paths = [];

                if ($prod)
                {
                    $xml->versioning->production = $obj->production;
                    $paths[] = PATH_TO_VERSION . $_prod;
                }

                file_put_contents(HOME . 'config.xml', $xml->saveXML());

                if (isset($obj->set))
                {
                    $rename = PATH_TO_VERSION . $obj->to;
                    $to = PATH_TO_VERSION . $obj->set;

                    @rename($rename, $to);
                    // add note.
                    $word = "Rollback was made from version {$obj->to} to {$obj->set}.";
                    $notepath = $to . '/release-note.md';
                    $date = "\n[".date('Y-m-d g:i:s a')."]\n";
                    $newword = $date . trim($word) . "\n";
                    $fo = fopen($notepath, 'a+');
                    fwrite($fo, $newword);
                    fclose($fo);
                }

                // add note
                if ($this->releaseNote !== null)
                {
                    foreach ($paths as $i => $pa)
                    {
                        if (is_dir($pa))
                        {
                            $notepath = $pa . '/release-note.md';
                            $fo = fopen($notepath, 'a+');
                            fwrite($fo, $this->releaseNote);
                            fclose($fo);
                        }
                    }
                }
                else
                {
                    $this->releaseNote = 'Performed a rollback operation.';
                }

                // delete rollback file
                @unlink($dir.$version);

                $this->recordTransaction('rollback', $this->releaseNote);

                // done
                echo json_encode(["success" => "Rollback was successful", "prod" => $obj->production]);
            }
            else
            {
                $this->warning("Invalid JSON Data. Operation Canceled!");
            }
        }
        else
        {
            $this->warning("Rollback {$version} not found on production server!");
        }
    }

    // change version title
    private function _set()
    {
        $version = strip_tags($_GET['version']);
        $replace = strip_tags($_GET['replace']);
        $template = $_POST['template'];

        $dir = PATH_TO_VERSION;
        $rename = $dir . $version;
        $with = $dir . $replace;

        if (is_dir($rename))
        {
            @rename($rename, $with);

            // try update config.xml 
            $xml = simplexml_load_file(HOME . 'config.xml');
            $prod = (array) $xml->versioning->production;
            $prod = $prod[0];
            $dev = (array) $xml->versioning->development;
            $dev = $dev[0];

            $action = $version;

            $version = json_encode(['production' => $prod, 'development' => $dev, 'set' => $version, 'to' => $replace]);
            $filename = preg_replace('/(-|:|\s*)/', '', date('Y-m-d g:i:s'));
            $filename .= '_rollback.json';

            @file_put_contents(PATH_TO_VERSION . 'Rollbacks/' . $filename, $version);

            if ($prod == $action)
            {
                $xml->versioning->production = $replace;
                $prod = $replace;
            }

            @file_put_contents(HOME . 'config.xml', $xml->saveXML());

            // decrypt path
            $template = str_replace('__PATH__/', PATH_TO_VERSION . $replace . '/', $template);
            $template = str_replace('__PATH__', PATH_TO_VERSION . $replace . '/', $template);
            $pathsxml = $with . '/paths.xml';
            @file_put_contents($pathsxml, $template);

            // note
            if ($this->releaseNote != null)
            {
                $fo = fopen($notepath, 'a+');
                fwrite($fo, $this->releaseNote);
                fclose($fo);
            }
            else
            {
                $this->releaseNote = 'Version Release title changed to '.$replace;
            }   

            $this->recordTransaction('set', $this->releaseNote);

            echo json_encode(['success' => 'SET completed']);
        }
        else
        {
            $this->warning("Version {$version} not found!");
        }
    }

    // pull version
    private function _pull()
    {
        $version = $_GET['version'];
        $id = $_GET['id'];
        $verify = isset($_GET['verify']) ? $_GET['verify'] : false;
        $dir = PATH_TO_VERSION . $version;
        $delete = isset($_GET['delete']) ? $_GET['delete'] : false;

        if ($delete === false)
        {
            if (is_dir($dir))
            {
                if ($verify !== false)
                {
                    echo json_encode(['success' => 'pulled']);
                }
                else
                {
                    $zip = new ZipArchive();
                    $zipfile =  PATH_TO_STORAGE . 'Tmp/' . $id . $version .'.zip';

                    if ($zip->open($zipfile, ZipArchive::CREATE) === true)
                    {
                        $data = glob($dir .'/*');
                        $dirs = [];

                        foreach ($data as $i => $f)
                        {
                            if (is_dir($f))
                            {
                                $dirs[] = $f;
                            }
                            else
                            {
                                if (basename($f) != 'paths.xml' && basename($f) != 'release-note.md')
                                {
                                    $dirs[] = $f;
                                }
                            }
                        }

                        foreach ($dirs as $y => $f)
                        {
                            $dr = getAllFiles($f);                                
                            $single = reduce_array($dr);

                            if (count($single) > 0)
                            {
                                foreach ($single as $z => $d)
                                {
                                    if ($d !== null)
                                    {
                                        $zip->addFile($d);
                                    }
                                }
                            }
                        }

                        $zip->close();
                    }

                    $this->recordTransaction('pull', 'Pulled a copy of '.$version);

                    ob_start();
                    $mime = mime_content_type($zipfile);
                    header("Content-Type: {$mime}");
                    header("Content-Disposition: attachment; filename=".$version.'.zip');
                    header('Content-Description: File Transfer');
                    header('Expires: 0');
                    header("Cache-Control: must-revalidate");
                    header("Pragma: public");
                    header("Content-Length: ". filesize($zipfile));
                    ob_flush();
                    readfile($zipfile);
                    exit();
                }
            }
            else
            {
                $this->warning("Version '{$version}' doesn't exists.");
            }
        }
        else
        {
            $path = $_GET['path'];
            @unlink($path);
        }
    }

    // peek versions
    private function _peek()
    {
        $data = glob(PATH_TO_VERSION .'*');
        $dirs = [];
        foreach ($data as $i => $f)
        {
            if (is_dir($f) && basename($f) != 'Rollbacks')
            {
                $dirs[] = $f;
            }
        }

        $xml = simplexml_load_file(HOME . 'config.xml');
        $prod = (array) $xml->versioning->production;
        $dev = (array) $xml->versioning->development;

        echo json_encode(['success' => 'peeked',
                        'prod' => $prod[0],
                        'dev' => $dev[0],
                        'dir' => $dirs]);
    }

    // Record Transaction taken.
    private function recordTransaction($type, $note)
    {
        if (!is_null($this->authUserid))
        {   
            $this->db->table('VcsHistory')->insert(
                'VcsUsersid,
                TransactionType,
                Transaction'
            )->bind(
                $this->authUserid,
                $type,
                $note
            );
        }
    }
}