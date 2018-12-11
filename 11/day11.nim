import strutils

proc getPowerLevel(x: int, y: int, gridSerial: int): int =
  var
    rackID: int
    level: int

  rackID = x + 10
  level = rackID * y
  level += gridSerial
  level *= rackID
  level = (level div 100) mod 10
  level -= 5

  return level

proc getMax(grid: array[300, array[300, int]], size: int): array[3, int] =
  var
    maxLevel, maxX, maxY, level: int

  for x in 0 .. 299 - size:
    for y in 0 .. 299 - size:
      level = 0
      for x2 in x .. x - 1 + size:
        for y2 in y .. y - 1 + size:
          level += grid[y2][x2]

      if (level > maxLevel):
        maxLevel = level;
        maxX = x;
        maxY = y;

  return [maxX, maxY, maxLevel];


var
  grid: array[300, array[300, int]]
  gridSerial: int
  res: array[3, int]
  maxRes: array[3, int]

gridSerial = strutils.parseInt(readFile("input.txt").strip())

for x in 0..299:
  for y in 0..299:
    grid[y][x] = getPowerLevel(x, y, gridSerial)

res = getMax(grid, 3);
echo "Part 1: ", res[0], ",", res[1], " (", res[2], ")"

for s in 1..300:
  echo s
  res = getMax(grid, s);
  if res[2] > maxRes[2]:
    maxRes = res

echo "Part 2: ", maxRes[0], ",", maxRes[1], " (", maxRes[2], ")"
