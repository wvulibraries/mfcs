# Use an official CentOS 6 image as the base
FROM centos:6

# Copy your PHP application files into the container
COPY . /var/www/html/

# Expose port 80 to the outside world
EXPOSE 80

# Start Apache in the foreground
CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/html/"]



