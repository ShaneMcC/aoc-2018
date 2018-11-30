# Advent of Code 2018

```
           .__.
         .(\\//).
        .(\\()//).
    .----(\)\/(/)----.
    |     ///\\\     |
    |    ///||\\\    |
    |   //`||||`\\   |
    |      ||||      |
    |      ||||      |
    |      ||||      |
    |      ||||      | Advent of Code 2018
    '------====------' - ShaneMcC
```

My PHP Solutions for [http://adventofcode.com](http://adventofcode.com) 2018

## Running

All of the solutions have their input as `input.txt` and some test input as `test.txt`, and will accept input from STDIN.

Solutions are run (for example day 1) as `./1/run.php` from the root directory.

There is also some command-line flags to alter how the scripts run.

```
$ ./1/run.php --help
Usage: ./1/run.php [options]

Valid options
  -h, --help               Show this help output
  -t, --test               Enable test mode (default to reading input from test.txt not input.txt)
  -d, --debug              Enable debug mode
      --file <file>        Read input from <file>

Input will be read from STDIN in preference to either <file> or the default files.
$
```

Solutions can also be run in a docker-container using (for example day 1) `./docker.sh 1` from the root directory. Command-Line flags can be passed after the day number, eg:
```
$ ./docker.sh 1 --help
Usage: /code/1/run.php [options]

Valid options:
  -h, --help               Show this help output
  -t, --test               Enable test mode (default to reading input from test.txt not input.txt)
  -d, --debug              Enable debug mode
      --file <file>        Read input from <file>

Input will be read from STDIN in preference to either <file> or the default files.
$
```
