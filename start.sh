#up containers
./vendor/bin/sail up -d 

#composer install
./vendor/bin/sail composer install

#start database
./vendor/bin/sail artisan migrate --seed

#set env file
cp .env.example .env

#generate docs
./vendor/bin/sail artisan scribe:generate

#cache app
./vendor/bin/sail artisan optimize




