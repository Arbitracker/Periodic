========
Periodic
========

Periodic is a fully unit tested PHP based task runner. It is supposed to
deliver a basic implementation for managing all kinds of recurring tasks and
events inside your web application. It has been designed with having all kinds
of different web hosting environments in mind. It is capable of running on most
shared hosting systems as well as root servers.

Motivation
==========

We needed some kind of recurring task management system for different projects
we are currently working on. We first started working on some kind of
CronjobIterator which is capable to parse a cron definition -- you might know
this definitions from the famous `vixie-cron`__ daemon -- and provide an
iterable list of timestamps matching this definition. The vcron__ definition
syntax seems to be the most intuitive and widely spread syntax to describe
recurring events, therefore we chose it.

We wrote a design document for it and started discussing it. We tried to create
a modular and flexible system fulfilling the needs of multiple of our projects.

__ ftp://ftp.isc.org/isc/cron/
__ ftp://ftp.isc.org/isc/cron/

Building / Testing
==================

To build the project just type::

    git submodule init
    git submodule update
    composer.phar install
    ant

The first two commands are only needed when building for the first time. The
``ant`` command should run the tests and all verifications.

Software Design
===============

Requirements
------------

The requirements for Periodic are to execute a configurable list of tasks at
specified times, or time intervals.

The execution of the tasks should be possible to be triggered in three ways:

- By a daemon, only supervising the task execution
- By a cronjob, which checks from time to time, if there are any open tasks to
  be run.
- Using a website, which triggers all open tasks.

The different execution models are necessary to support different usage
profiles: Running on pure build bots, running on dedicated servers or running
on shared hosting.

The tasks itself are specified by names in the cron table and configured using
XML files. The XML files contain a set of commands to execute. The available
commands start with general shell commands and file operations, and should be
extensible with application dependent commands. Each command should be able to
receive a arbitrary set of configuration values.

Design
------

The design of this components splits up in to four main parts, the execution,
the crontab, the task specification and the commands itself.

The execution
^^^^^^^^^^^^^

The executor, being a called script, or a daemon, has to execute all tasks,
which are pending since the last call. No tasks may be executed multiple
times.

During the execution of any tasks, the executor should maintain a lock, to
ensure no task is run multiple times in parallel, which may lead to conflicts
or deadlocks.

If the executor dies, it might not release its lock. It should be configurable
after which time a lock can be automatically released, or if it should only be
released manually by the user.

The cron table
^^^^^^^^^^^^^^

The crontab follows the cron tables, commonly known from Unix cron
implementations, unless the command is replaced by a task name. A simple cron
table, scheduling a task "periodic-test-task" for each 15 minutes, would look
like::

    */15 * * * * group:periodic-test-task

The executor parses the cron table, calculates the pending task since the last
call and executes them in order.

Task in one group may not be executed in parallel by the executor. The task
groups are optional and prepended to the task name, seperated by a colon.

Task specification
^^^^^^^^^^^^^^^^^^

The tasks are specified using a XML file, which can be found in a directory
specified to the executor, using the name $task.xml, where $task is the name
of the task given in the cron table.

The XML file should basically look like::

    <?xml version="1.0"?>
    <task>
        <config>
            <reScheduleTime>92384032</>
            <timeout>92384032</>
        </config>

        <command type="shell">
            <!-- ... -->
        </command>
        <command type="vcsWrapperUpdate">
            <!-- ... -->
        </command>
        <!-- ... -->
    </task>

The command type is a mandatory attribute, specifying the type of command,
which should be instantiated by the executor.

Each task may optionally contain a set of configuration directives, which
define the tasks behaviour in case of errors or other special return states.
The possible return states of commands are:

- Reschedule

  The task should be rescheduled, for example because a resource is
  temporarily is not available.

- Abort

  A command may return "abort", to indicate that the execution of the further
  commands in the task may not be sensible.

- Failure

  A command failed to execute, the task should indicate this with a logged
  error.

- Success

  A command has been executed successfully, further execution of the other
  commands should be no problem.

Each command node may contain an arbitrary set of XML elements, to configure
the execution of the given command.

Commands
^^^^^^^^

All command classes inherit from periodicCommand, which specifies the
constructor, which takes the command XML subtree from the task specification
file as its configuration, and a execute() method::

    abstract class periodicCommand
    {
        abstract public function __construct( arbitXmlNode $configuration );

        /**
         * Execute command and return false on failure.
         *
         * @return status
         */
        abstract public function execute();
    }

All commands must be registered in the periodicCommandRegistry under the name,
which correlates to the type attribute in the task specification and are
instantiated by the executor.

Global concerns
---------------

There are some global concerns which are shared by all parts of Periodic, and
may also need integration with the application using Periodic.

Logging
^^^^^^^

(Long running) scripts running in the background or are performed periodically
in the background need logging to stay debugable and maintainable.

Periodic should offer a logger interface, which can be implemented, and which
instances can be passed to the executor, so the task and command execution can
be logged. A basic file based logging mechanism might be implemented as a
default logging mechanism.

The logging interface could be something simple as::

    interface periodicLogger
    {
        const INFO    = 1;
        const WARNING = 2;
        const ERROR   = 4;

        public function log( (toString) $message, $severity = self::INFO );
        public function setTask( periodicTask $task );
        public function setCommand( periodicCommand $command );
    }



..
   Local Variables:
   mode: rst
   fill-column: 79
   End: 
   vim: et syn=rst tw=79
