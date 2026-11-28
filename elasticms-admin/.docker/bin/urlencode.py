#!/usr/bin/env python3

import urllib.parse
import sys

print(urllib.parse.quote_plus(sys.argv[1]))