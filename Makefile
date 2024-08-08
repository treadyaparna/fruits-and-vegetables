run-setup:
	docker compose build
	docker compose up -d
	docker exec php /bin/sh -c "composer install && php bin/console doctrine:migrations:migrate"

run-app:
	docker compose build
	docker compose up -d

kill-app:
	docker compose down
	docker system prune

add-data: # populate data
	docker exec php /bin/sh -c "php bin/console app:process-item-file request.json"

enter-app: # enter the php docker container
	docker exec -it php /bin/sh

enter-db: # enter the mysql docker container
	docker exec -it mysql /bin/sh -c "mysql -u user -p" # enter the password to enter the mysql docker container

generate-migration: # generate migration file
	docker exec php /bin/sh -c "php bin/console doctrine:migrations:diff"

run-tests: # run all unit tests
	docker exec php /bin/sh -c "./vendor/bin/phpunit"