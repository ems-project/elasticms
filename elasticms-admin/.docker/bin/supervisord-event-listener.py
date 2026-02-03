#!/usr/bin/python3
import sys
import subprocess

def write_stdout(s):
    sys.stdout.write(s)
    sys.stdout.flush()

def write_stderr(s):
    sys.stderr.write(s)
    sys.stderr.flush()

def main(args):
    while 1:
        # transition from ACKNOWLEDGED to READY
        write_stdout('READY\n')

        # read header line from stdin
        line = sys.stdin.readline()
        write_stderr(line)

        # read event payload and print it to stderr
        headers = dict([ x.split(':') for x in line.split() ])
        data = sys.stdin.read(int(headers['len']))
        write_stderr(data)

        res = subprocess.call(args, stdout=sys.stderr); # don't mess with real stdout

        # transition from READY to ACKNOWLEDGED
        if res != 0:
            write_stdout('RESULT 4\nFAIL')
        else:
            write_stdout('RESULT 2\nOK')

if __name__ == '__main__':
    main(sys.argv[1:])