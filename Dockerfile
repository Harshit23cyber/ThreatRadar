FROM php:8.2-cli

# Install necessary system dependencies and CA certificates for cURL
RUN apt-get update && apt-get install -y \
    curl \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Set the working directory
WORKDIR /app

# Copy the application code into the container
COPY . .

# Expose the default Render port (Render injects PORT dynamically)
EXPOSE 10000

# Start the PHP built-in server using the custom router
CMD php -S 0.0.0.0:${PORT:-10000} router.php
