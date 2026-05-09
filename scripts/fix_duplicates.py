import re

# Read the original PostgreSQL dump
with open('localhost_forerent.sql', 'r') as f:
    content = f.read()

# Find the billing_items COPY block
pattern = r'COPY public\.billing_items[^\n]*\nFROM stdin;\n(.*?)\n\\\.'
match = re.search(pattern, content, re.DOTALL)

if match:
    copy_data = match.group(1)
    
    # Split into lines and track seen IDs
    lines = copy_data.split('\n')
    seen_ids = set()
    unique_lines = []
    
    for line in lines:
        if line.strip():
            # Extract first field (billing_item_id)
            fields = line.split('\t')
            if fields:
                item_id = fields[0]
                if item_id not in seen_ids:
                    seen_ids.add(item_id)
                    unique_lines.append(line)
    
    # Now regenerate with unique data
    print(f"Found {len(unique_lines)} unique billing_item records (removed {len(lines) - len(unique_lines)} duplicates)")
    
    # Replace the COPY block with unique data
    unique_data = '\n'.join(unique_lines)
    new_block = f'COPY public.billing_items (billing_item_id, billing_id, charge_category, charge_type, description, amount, created_at, updated_at, deleted_at) FROM stdin;\n{unique_data}\n\\.'
    new_content = content[:match.start()] + new_block + content[match.end():]
    
    # Write modified dump back
    with open('localhost_forerent_dedup.sql', 'w') as f:
        f.write(new_content)
    
    print("✓ Created localhost_forerent_dedup.sql with deduplicated billing_items")
else:
    print("✗ Could not find billing_items COPY block")
