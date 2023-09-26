# Hackernews Data Spooler

## Description

The HackerNews Data Spooler is a Laravel-based project that efficiently retrieves data from the HackerNews API and stores it in a database. This project employs concepts such as jobs, queues, services, and containers to handle data processing effectively.

## Key Features

-   Data spooling from the HackerNews API.
-   Separate database tables for each item entity (stories, comments, authors, etc.) following the HackerNews API structure.
-   Prevention of duplicate items.
-   Scheduled data spooling every 12 hours.
-   Spooling of up to 100 stories along with all related items, including authors, comments, and comment-authors.

## Table of Contents

-   [Prerequisites](#prerequisites)
-   [Installation and Usage](#installation)
-   [Testing](#testing)

### Prerequisites

Before you begin with the HackerNews Data Spooler project, ensure that you have the following prerequisites in place:

-   PHP: Ensure that you have PHP installed on your system, preferably PHP 7.4 or later. You can download PHP from [php.net](https://php.net).

-   **Composer**: Laravel uses Composer for dependency management. If you don't have Composer installed, you can download it from [getcomposer.org](https://getcomposer.org).

-   **MySQL Database**: Prepare a MySQL database for your project. You'll need to create a new database and update your `.env` file with the credentials.

-   **Queue Configuration**: Ensure that your Laravel project is correctly configured to use a queue driver (e.g., Redis, database). Refer to the [Laravel Queue documentation](https://laravel.com/docs/10.x/queues) for configuration details.

-   **Postman (Optional)**: Download or install it [here](https://postman.com)

### Installation and Usage

To install the project and set it up on your local development environment, follow these steps:

1. **Clone this Repository**: Begin by cloning this repository to your local machine using Git. Open your terminal and run the following command:

```bash
git clone https://github.com/davydocsurg/hackernews-spooler.git
```

2. **Navigate to the Project Directory**: Change your working directory to the project folder:

```bash
cd hackernews-spooler
```

3. **Install Composer Dependencies**: Laravel uses Composer to manage its dependencies. If you don't have Composer installed, you can download it from [getcomposer.org](https://getcomposer.org). Once Composer is installed, run the following command to install the PHP dependencies:

```bash
composer install
```

4. **Set Up Environment Variables**: Create a copy of the `.env.example` file and name it `.env`. You can do this by running:

```bash
cp .env.example .env
```

Then, open the `.env` file and configure the database connection settings according to your MySQL setup.

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

Replace `your_database_name`, `your_database_username`, and `your_database_password` with your MySQL database credentials.

In addition to this, set your `QUEUE_CONNECTION` to `database`:

```bash
QUEUE_CONNECTION=database
```

5. **Generate Application Key**: Generate an application key for Laravel by running the following command:

```bash
php artisan key:generate
```

6. **Run Database Migrations**: Run the database migrations to create the necessary database tables:

```bash
php artisan migrate
```

7. **Start the Development Server**: You can now start the Laravel development server:

```bash
php artisan serve
```

The application should now be running locally at http://localhost:8000.

8. **Start the Queue Worker**:

```bash
php artisan queue:work
```

This command will process any queued jobs, such as fetching stories, comments, and their replies from the Hackernews API.

9. **Run the Data Spooler**: The data spooler can be run using one of the following options:

-   **Schedule the Data Spooler (Local Development)**: To schedule and run the job that fetches and stores Hackernews data on your local development server, you can use Laravel's built-in scheduler. Follow these steps:

a. Open your terminal or command prompt on your local machine.

b. Change your working directory to the project folder where you've cloned the Hackernews Spoofer repository:

```bash
cd /path-to-your-project
```

Replace `/path-to-your-project` with the actual path to your project directory.

c. Edit Your Local Cron Jobs

Laravel's scheduler relies on the cron job system to execute tasks at specified intervals. To schedule the data spooler, you'll need to add a new cron job. To edit your user's cron jobs, run the following command:

```bash
crontab -e
```

d. Add the Laravel Scheduler Cron Job

In the text editor that opens, add the following line to schedule the Laravel scheduler to run every 12 hours:

```bash
0 */12 * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Replace `/path-to-your-project` with the actual path to your project directory.

This cron job will execute Laravel's scheduler every 12 hours.

e. Save and Exit

Save the file and exit the text editor. Now, the data spooler will be automatically executed by Laravel's scheduler every 12 hours.

-   **Use the Artisan Command (Manually)**: You can manually trigger the data spooler using the following Artisan command:

```bash
php artisan spool:fetch-stories
```

The above command will fetch 100 stories alongside their comments and replies. However, if you want to specify the number of stories you want to fetch, you can add the `--limit` flag and specify the number of stories you want to fetch like this:

```bash
php artisan spool:fetch-stories --limit=10
```

Now, this command will fetch 10 stories alongside their comments and replies.

-   **Use Postman**

1. Open Postman or [download](https://postman.com) it if you haven't already.
2. If your development server is not running, run this command in the **project's root directory** to start it:

```bash
php artisan serve
```

3. In Postman, Create and make a `GET` request to this endpoint:

```bash
{{your-host}}/api/fetch-stories
```

4. Monitor the response to confirm the successful execution of the data spooling process.

### Testing

To run the tests for the HackerNews Data Spooler project, you can use Laravel's built-in testing tools. Here are the steps to run the tests:

1.  Open your terminal
2.  **Navigate to the Project Directory**: Change your working directory to the project folder where you've cloned the HackerNews Spooler repository if you're not already in that directory:

```bash
cd /path-to-your-project
```

Replace `/path-to-your-project` with the actual path to your project directory.

3. **Run the Tests**: Use the following Artisan command to run the tests:

```bash
php artisan test
```

After running the tests, you will see the results displayed in your terminal.
