#!/usr/bin/python2
import re
from collections import defaultdict
from z3 import *

def gan(s):
  return map(int, re.findall(r'-?\d+', s))
def lenr(l):
  return xrange(len(l))

with open("input.txt") as input_file:
    d = input_file.read().strip().splitlines()


nanobots = map(gan, d)
nanobots = [((n[0], n[1], n[2]), n[3]) for n in nanobots]

def dist((x0, y0, z0), (x1, y1, z1)):
  return abs(x0-x1) + abs(y0-y1) + abs(z0-z1)

srad = 0
rad_idx = 0
in_range = defaultdict(int)
for i in lenr(nanobots):
  pos, rng = nanobots[i]
  strength = 0
  if rng > srad:
    srad = rng
    rad_idx = i
    for j in lenr(nanobots):
      npos, _ = nanobots[j]
      if dist(pos, npos) <= rng:
        in_range[i] += 1

print "Part 1: ", in_range[rad_idx]

def zabs(x):
  return If(x >= 0,x,-x)

(x, y, z) = (Int('x'), Int('y'), Int('z'))
in_ranges = [
  Int('in_range_' + str(i)) for i in lenr(nanobots)
]
range_count = Int('sum')
o = Optimize()

for i in lenr(nanobots):
  (nx, ny, nz), nrng = nanobots[i]
  o.add(in_ranges[i] == If(zabs(x - nx) + zabs(y - ny) + zabs(z - nz) <= nrng, 1, 0))

o.add(range_count == sum(in_ranges))
dist_from_zero = Int('dist')
o.add(dist_from_zero == zabs(x) + zabs(y) + zabs(z))
h1 = o.maximize(range_count)
h2 = o.minimize(dist_from_zero)
o.check()

print in_ranges

print "Part 2: ", o.lower(h2), "(x:", o.model()[x], "y: ", o.model()[y], "z: ", o.model()[z], ")"
