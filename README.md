# Project Setup and Usage
This guide will help you set up and run the project using Docker, run database migration, and test the API endpoints.

Prerequisites
- Docker and Docker Compose installed on your system.
- PHP installed on your system.

**Step 1: Run Docker Compose**
Navigate to the project directory containing the docker-compose.yaml file.

Run the following command to start the Docker containers:
```
docker compose -f "detik/docker-compose.yaml" up -d --build
```

**Step 2: Bash Into PHP Container**
List the available containers to find the PHP container's name or ID:
```
docker ps
```

Bash into the PHP container using its name or ID:
```
docker exec -it CONTAINER_NAME_OR_ID /bin/bash
Replace CONTAINER_NAME_OR_ID with the actual name or ID of the PHP container. 
```

For example:
```
docker exec -it f10c7b01bd06 /bin/bash
```

**Step 3: Run Database Migration**
Inside the PHP container, run the following command to execute the database migration:
```
php initial-migration.php
```


**Step 4: Run Tests**
Run the following commands to execute the tests:
```
php api-test.php
php transaction-cli-test.php
```

**Step 5: Test the API Endpoints**
You can test the API endpoints using curl or a tool like Postman. Here are some curl examples:

Create Transaction
```
curl --location 'localhost:8080/transactions' \
--header 'Content-Type: application/json' \
--data '{
    "invoice_id": 12312,
    "item_name": "Laptop",
    "amount": 10,
    "payment_type": "credit_card",
    "customer_name": "Adam",
    "merchant_id": 100
}'
```

Get Transaction
Replace {reference_id} and {merchant_id} with values from the previous API response.
```
curl --location 'localhost:8080/transactions?references_id={reference_id}&merchant_id={merchant_id}'
```

Update Transaction
Replace {reference_id} with the actual reference ID.
```
curl --location --request PATCH 'localhost:8080/transactions' \
--header 'Content-Type: application/json' \
--data '{
    "references_id": "{reference_id}",
    "status": "failed"
}'
```