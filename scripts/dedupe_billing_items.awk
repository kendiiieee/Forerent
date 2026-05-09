BEGIN {
    in_table = 0
    table_name = ""
    row_num = 0
}

/^INSERT INTO `billing_items`/ {
    in_table = 1
    print
    next
}

in_table && /^;$/ {
    in_table = 0
    # Remove trailing comma and newline from last row, add semicolon
    printf ";\n\n"
    next
}

in_table && /^\(/ {
    # Extract the billing_item_id (first field after '(')
    match($0, /\('([^']+)'/, arr)
    id = arr[1]
    
    # Skip if we've seen this ID before
    if (seen[id]) {
        next
    }
    seen[id] = 1
    row_num++
    
    # Remove trailing comma if present, we'll add it back
    gsub(/,\s*$/, "", $0)
    
    # Print with comma (except we'll handle the last row)
    print $0 ","
    next
}

# For all other content
{
    print
}

END {
    # If we were in billing_items, make sure we have the semicolon
    # (already handled above, but just in case)
}
