# Use the official PHP image as the base image
FROM php:7.4-cli

# Set the working directory inside the container
WORKDIR /app

# Install any dependencies your PHP application needs
# For example, if you need PDO for database access:
RUN docker-php-ext-install pdo pdo_mysql


# Copy the PHP API file into the container
# COPY api.php .
COPY . .

# Expose port 80 (HTTP) for the API
EXPOSE 80

# Command to run the PHP script
CMD ["php", "-S", "0.0.0.0:80", "api.php"]
