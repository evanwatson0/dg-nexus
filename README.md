# DG Nexus

A tool used to analyse and visualise drug-gene interactions. Filter by relationship types and visualise in our plot. You can also generate
LLM reports analysing notable interactions, providing relevant references
to current (and existant) research on these interactions.

Utilises Data from [DGI-DB](https://dgidb.org/). Works on local set up,
where users can store drug gene interactions of interest

## Navigation

dataflow - contains files used for the storage/retrieval of drug gene interactions

database - contains dump files and implementation of sql database

frontend - contains all elements used directly/indirectly in frontend

llm - contains all files used for the storage/retrieval/request of the Large Language Model Implementation

# Set Up

## Javascript
On base directory, run:

```
npm install
```

## PHP
Ensure the following packages are installed (In php-packages.txt)

```
bcmath
curl
dom
mysqli
pdo_mysql
readline
simplexml
xml
xmlreader
xmlwriter
```

## Set up Mysql Database
See the [database setup guide](database/readme.md) for documentation on setting up the MySQL database.

Then, open 'config.php' and enter in parameters for DB_HOST, DB_NAME, DB_USER, DB_PASS corresponding to your MySQL database set up

## Setting LLM API Token
Note: This project uses OpenAI's API to make requests using ChatGPT models. 
Users must generate a key to use the LLM features

### Generating a Token
Go to OpenAI's [website](https://platform.openai.com/docs/quickstart?desktop-os=windows) to generate a key

### Storing a Key
#### On Windows 
type into directory
```
$setx OPENAI_API_KEY "sk-your-key-here"
```

#### On MacOS
type into directory
```
echo 'export OPENAI_API_KEY="sk-your-key-here"' >> ~/.zshrc
source ~/.zshrc
```
check it is stored correctly
```
echo $OPENAI_API_KEY
```

## Running the Program

To run this program, run a php server on the following file

'frontend/login.php'