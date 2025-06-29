# Docker
Build the Docker image for the Framez project:
```
docker build -f docker/Dockerfile -t test-image .
```
Run the Docker container:
```
docker run --rm -p 3000:3000 test-image
```