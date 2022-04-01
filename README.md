# simplesamlphp-module-sqlotp
Simplesamlphp module for one time usable passwords stored in **sql

# Installation
```
cd <Simplesamlphp Install Directory>/modules
git clone https://github.com/phavekes/simplesamlphp-module-sqlotp.git sqlotp
```

Edit <Simplesamlphp Install Directory>/config/authsources.php and add:

```
$config = [
    'otp' => array(
      'sqlotp:OtpAuth',
      'dsn' => 'mysql:host=localhost;dbname=otp',
      'username' => 'database-username',
      'password' => 'database-password',
      'basedomain' => 'your.domain.name'
     ),

```
  
  
  Create a database and a user. Create a table in this database
  ```
  CREATE TABLE `users` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `givenName` varchar(1024) NOT NULL,
 `sn` varchar(1024) NOT NULL,
 `mail` varchar(1024) NOT NULL,
 `schachome` varchar(1024) NOT NULL,
 `password` varchar(32) NOT NULL,
 `used` int(11) NOT NULL DEFAULT '0',
 `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10000 
  ```
  
# Create users

To add users a script is provided in the `/scripts/` directory. Edit the database-parameters in the first lines to match your username and password.
The scipt exppects a file named `import.csv` containing:

```
firstname1,surname1,email1,homeinstitution1
firstname2,surname1,email2,homeinstitution2
firstname3,surname1,email3,homeinstitution1
firstname4,surname1,email4,homeinstitution1
```

Run the script with:

```
php import.php
```

The script wil create a csv-file `output.csv` in the same format, adding a One Time Password and a eppn to the end of eah line.



