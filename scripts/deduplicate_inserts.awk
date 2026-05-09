BEGIN {
    in_billing_items = 0
    table_name = ""
    row_count = 0
}

/^INSERT INTO `billing_items`/ {
    in_billing_items = 1
    print
    next
}

in_billing_items && /^;$/ {
    in_billing_items = 0
    print
    next
}

in_billing_items && /^\(/ {
    # Extract the billing_item_id (first field after '(')
    match($0, /\('([^']+)'/, arr)
    id = arr[1]
    
    # Skip if we've seen this ID before
    if (seen[id]) {
        next
    }
    seen[id] = 1
    
    # Print with comma, except for first row (which already has comma in original)
    print
    next
}

# For all other tables, print as-is
{
    print
}
