# ATF-Php
**Another Tiny Framework for PHP**

Small, fast and lightweight MVC framework for PHP 5.5+

## State of development
This software is in early alpha status. Not ready for productive use (yet)

### Requirements
PHP 5.4+ running on a Webserver (e.g. Apache2) with mod_rewrite enabled  

    # a2enmod rewrite  
    # /etc/init.d/apache2 reload
A Vhost with an environmental variable 'ENVIRONMENT' set to either 'debug', 'staging' or 'live'. If nothing is defined, the environments defaults to 'debug'  

    SetEnv ENVIRONMENT "debug"

### Why not take an existing framework?
Existing Frameworks have often been under development for years, which is cool as it shows the need for such code as well as the passions the developers share. But it also results in a huge codebase and lots of legacy code to keep everthing compatible. You end up with a big bunch of code and you have to adapt your complete project to the frameworks prequirements. And finally you have your sourcode depending on thousands of lines of source that you actually dont know anything about (except the way to use it).

### What can ATF-Php do better?
Simply by starting a framework now rather than in the past ATF-Php can completely forget about any old(er) PHP Versions. ATF-Php starts right away with all the beauty that PHP 5.4 offers with no need to stay compatible or maintain legacy code. 

### Why this reduced set of features?
ATF-Php comes with a set of (core) features used in most web-projects. Additional features will have to be coded individually to match the concerning project (see pholosophy)

### ATF-Php philosophy
Once Version 1.0 is released, this project will be considered 'done'. There will not be a Version 2.0 whatsoever. The plan is to only implement new PHP features and (of course) fix any issues that occur. The main set of featres will not be expanded.

That way ATF-Php remains 'tiny' and yet meets the demands of an arbitrary web project, while still enabling the developer to read the complete underlying framework code within a few hours. This provides a deep understanding of the complete proccess during a request and results in better, faster and easier to maintain sourcecode.

### But I prefer another philosophy
Well, in that case you should consider: 
* Using another PHP framework
* Creating a Fork of ATF-Php (thx to MIT licence)
* Writing your own framework (and publishing it under open-source) ;)

### Core Features
* Application Routing (using webserver rewrite)
* Models (simple and secure usage without worrying about sql injections...)
* Controller Classes (for a clean structure)
* View Templates (using the template engine of your choice)
* Multi Language Support (using UTF-8 LanguagePacks in ini format)
* DB connections with PHP PDO Objects
* PHP 5.4+ OOP (with root namespace 'ATFApp')
* Simple Code Profiler
* PDO Query Cache
* Free choice of template engine
* Clean bootstrap proccess
* Autoloader for all relevant classes
* A complete framework in less than 10.000 lines of source (including PHPDoc-style comments)
* Freedom to design your application the way you prefer to



