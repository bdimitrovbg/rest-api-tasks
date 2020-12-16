# rest-api-tasks
REST API for simple bash tasks with dependencies.

#Install
1. Pull the repository
2. In project root execute
   
    2.1. `docker-compose build`
   
    2.2. `docker-compose up -d`

    2.3. `docker exec rest-api-tasks composer install`

#Tests
##Unit with phpunit
`docker exec rest-api-tasks bin/simple-phpunit`
##Functional with Postman
1. Import the postman collection located in `resources/postman/tasks_rest_api_postman_collection.json`
2. Run the collection and check the tests results.

#How to use
##Endpoints
###Context:
Both endpoints require request body of the following type:
<pre><code>
{
  "tasks": [
    {
      "name": "task-1",
      "command": "touch /tmp/file1"
    },
    {
      "name": "task-2",
      "command": "cat /tmp/file1",
      "requires": [
        "task-3"
      ]
    },
    {
      "name": "task-3",
      "command": "echo 'Hello World!' > /tmp/file1",
      "requires": [
        "task-1"
      ]
    },
    {
      "name": "task-4",
      "command": "rm /tmp/file1",
      "requires": [
        "task-2",
        "task-3"
      ]
    }
  ]
}
</code></pre>


###`POST` `http://localhost:4000/tasks/rest/v1/resolve-dependencies`
This endpoint will resolve dependencies for request body submitted tasks 
and will return response of the following type:
<pre><code>
[
  {
    "name": "task-1",
    "command": "touch /tmp/file1"
  },
  {
    "name": "task-3",
    "command": "echo 'Hello World!' > /tmp/file1"
  },
  {
    "name": "task-2",
    "command": "cat /tmp/file1"
  },
  {
    "name": "task-4",
    "command": "rm /tmp/file1"
  }
]
</code></pre>


###`POST` `http://localhost:4000/tasks/rest/v1/generate-bash-script`
This endpoint will resolve dependencies for request body submitted tasks
and will generate bash script suitable for execution.
<pre><code>
#!/usr/bin/env bash

touch /tmp/file1
echo "Hello World!" > /tmp/file1
cat /tmp/file1
rm /tmp/file1
</code></pre>

##Bash usage
`curl -d @mytasks.json http://localhost:4000/tasks/rest/v1/generate-bash-script | bash`

