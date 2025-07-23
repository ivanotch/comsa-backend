<pre> 
this backend folder should be in the same parent folder as the COMSA-NOW frontend folder:
project/          <---one parent folder
├── frontend/     <---Cloned COMSA-NOW 
├── backend/      <---Clone comsa-backend

after cloning install a composer and then run [composer require vlucas/phpdotenv] in the terminal inside the /comsa-backend directory
create a .env file and add the database information:
DB_DSN="mysql:host=localhost;dbname=databaseName"
DB_USER="databseUsername" (usually root)
DB_PASS="databasePass" (usually empty "")

run the sql query located in the schema file

Happy coding!
</pre>
