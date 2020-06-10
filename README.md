ODI Project: Questionnaire
========================

Our last mission

INSTALL
--------------
- Require PHP >=5.5.9
- Composer 1.5.6
- Setup the project:
    ~~~
     $ git clone https://github.com/sonnguyenthai/questionnaire.git
     $ cd questionnaire
     $ composer install
     $ php bin/console doctrine:database:create
     $ php bin/console	doctrine:schema:create
     $ php bin/console server:run
    ~~~
    
### Some helpful commands    
- For generating entities:
    ~~~
    $ php bin/console doctrine:generate:entities AppBundle
    ~~~
- For updating schema (run this command after changing schema to apply changes):
    ~~~
    $ php bin/console doctrine:schema:update --force
    ~~~  
    
- Does it succed?
    
Enjoy!


REFERENCES
---------------
- https://getcomposer.org/download/
- https://symfony.com/doc/3.4/setup.html

