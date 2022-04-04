<?php
  class sspmod_sqlotp_Auth_Source_OtpAuth extends sspmod_core_Auth_UserPassBase {

      /* The database DSN.
       * See the documentation for the various database drivers for information about the syntax:
       *     http://www.php.net/manual/en/pdo.drivers.php
       */
      private $dsn;

      /* The database username, password & options. */
      private $username;
      private $password;
      private $basedomain;
      private $options;

      public function __construct($info, $config) {
          parent::__construct($info, $config);

          if (!is_string($config['dsn'])) {
              throw new Exception('Missing or invalid dsn option in config.');
          }
          $this->dsn = $config['dsn'];
          if (!is_string($config['username'])) {
              throw new Exception('Missing or invalid username option in config.');
          }
          $this->username = $config['username'];
          if (!is_string($config['password'])) {
              throw new Exception('Missing or invalid password option in config.');
          }
          $this->password = $config['password'];
          if (!is_string($config['basedomain'])) {
              throw new Exception('Missing or invalid basedomain option in config.');
          }
          $this->basedomain = $config['basedomain'];
          if (isset($config['options'])) {
              if (!is_array($config['options'])) {
                  throw new Exception('Missing or invalid options option in config.');
              }
              $this->options = $config['options'];
          }
      }

      protected function login($username, $password) {

          /* Connect to the database. */
          $db = new PDO($this->dsn, $this->username, $this->password, $this->options);
          $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          /* Ensure that we are operating with UTF-8 encoding.
           * This command is for MySQL. Other databases may need different commands.
           */
          $db->exec("SET NAMES 'utf8mb4'");

          /* With PDO we use prepared statements. This saves us from having to escape
           * the username in the database query.
           */
          $st = $db->prepare('SELECT * FROM users WHERE id=:username');

          if (!$st->execute(array('username' => $username))) {
              throw new Exception('Failed to query database for user.');
          }

          /* Retrieve the row from the database. */
          $row = $st->fetch(PDO::FETCH_ASSOC);
          if (!$row) {
              /* User not found. */
              SimpleSAML\Logger::warning('sqlOTP: Could not find user ' . var_export($username, TRUE) . '.');
              throw new SimpleSAML_Error_Error('WRONGUSERPASS');
          }

          /* Check the password. */
          if (! password_verify($password,$row['password'])) {
              /* Invalid password. */
              SimpleSAML\Logger::warning('sqlOTP: Wrong password for user ' . var_export($username, TRUE) . '.');
              throw new SimpleSAML_Error_Error('WRONGUSERPASS');
          }

          /* Only allow login once. */
          if ($row['used']>0) {
              /* Was used before. */
              SimpleSAML\Logger::warning('sqlOTP: Password already used for user ' . var_export($username, TRUE) . '.');
              throw new SimpleSAML_Error_Error('WRONGUSERPASS');
          }

          /* Create the attribute array of the user. */
          $attributes = array(
              'urn:mace:dir:attribute-def:uid' => array($username),
              'urn:mace:dir:attribute-def:cn' => array($username),
              'urn:mace:dir:attribute-def:givenName' => array($row['givenName']),
              'urn:mace:dir:attribute-def:sn' => array($row['sn']),
              'urn:mace:dir:attribute-def:mail' => array($row['mail']),
              'urn:mace:dir:attribute-def:eduPersonPrincipalName' => array($row['id'].'@'.$row['schachome'].".".$this->basedomain),
              'urn:mace:terena.org:attribute-def:schacHomeOrganization' => array($row['schachome'].".".$this->basedomain),
              'urn:mace:dir:attribute-def:eduPersonAffiliation' => array('affiliate'),
              'urn:mace:dir:attribute-def:eduPersonScopedAffiliation' => array('affiliate'."@".$row['schachome'].".".$this->basedomain)
          );

          /* Update usage count for the user */
          $st = $db->prepare('UPDATE users SET used = used+1 WHERE id=:username');
          if (!$st->execute(array('username' => $username))) {
              throw new Exception('Failed to update counter for user ' . var_export($username, TRUE) . '.');
          }
          

          /* Return the attributes. */
          return $attributes;
      }

  }