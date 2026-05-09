import csv
import json
import glob
import os

out_path = 'data_csv/combined.csv'
files = sorted(glob.glob('data_csv/*.csv'))

with open(out_path, 'w', newline='', encoding='utf-8') as out:
    writer = csv.writer(out)
    writer.writerow(['table', 'data_json'])

    for f in files:
        tbl = os.path.splitext(os.path.basename(f))[0]
        with open(f, newline='', encoding='utf-8') as infile:
            reader = csv.DictReader(infile)
            for row in reader:
                # Convert Postgres NULL token \N to JSON null
                for k, v in row.items():
                    if v == '\\N':
                        row[k] = None
                writer.writerow([tbl, json.dumps(row, ensure_ascii=False)])

print('combined written to', out_path)
