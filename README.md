## First, run this command:
This command is used to install all the PHP dependencies listed in the composer.json file for your Laravel project.
> #### composer install
<br />

## Second, rename the env file:
This command copies the .env.example file and renames it to .env.
> #### cp .env.example .env
<br />

## Third, open a new terminal and run this command: 
This command resets the database by rolling back all migrations and then re-running them with the database seeders.
>#### php artisan mi:f --seed
<br />

## Fourth, open a new terminal and run this command: 
This command executes Laravel's scheduled tasks
> #### php artisan schedule:run
 <br />

## Fifth, open a new terminal and run this command: 
This command executes Laravel's scheduled tasks
> #### php artisan queue:work
 <br />
 
## sixth, run the project by running this command:
>#### php artisan ser
 <br />



