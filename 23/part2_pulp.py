#!/usr/bin/python3
'''
From: https://www.reddit.com/r/adventofcode/comments/a8sqov/help_day_23_part_2_any_provably_correct_fast/ecdnimh/
(shrug)
'''

import re
from pulp import *
from itertools import product

lines = open('input.txt').read().strip().split('\n')

bots = []
for i in range(len(lines)):
  x,y,z, r = list(map(int, re.findall(r'-?\d+', lines[i])))
  bots.append([x,y,z,r])

prob = LpProblem("problem", LpMaximize)

# Variables
counts = [LpVariable("c_{}".format(i), lowBound=0, upBound=1, cat='Integer') for i in range(len(bots))]
x = LpVariable("x")
y = LpVariable("y")
z = LpVariable("z")
totalCount = LpVariable("totalCount")

# Objective
prob += totalCount

# Constraints
prob += totalCount == sum(counts)

for i, (x_i, y_i, z_i, r_i) in enumerate(bots):
  c_i = counts[i]
  for sign in product([-1, 1], repeat=3):
    prob += ( sign[0] * (x - x_i) + sign[1] * (y - y_i) + sign[2] * (z - z_i) ) <= r_i + (1 - c_i) * int(1e10)

status = prob.solve()
print(LpStatus[status])
print('x,y,z', value(x), value(y), value(z))
print('distFromOrigin', abs(value(x)) + abs(value(y)) + abs(value(z)))

print('totalCount', value(totalCount))
