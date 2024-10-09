#composer install
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs

#up containers
./vendor/bin/sail up -d 

#start database
./vendor/bin/sail artisan migrate --seed --force

#set env file
cp .env.example .env

#generate docs
./vendor/bin/sail artisan scribe:generate

#cache app
./vendor/bin/sail artisan optimize




