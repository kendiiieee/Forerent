#!/usr/bin/awk -f

# Converts pg_dump COPY blocks into MySQL/TiDB INSERT statements.
# Intended for data-only migration after schema is created by Laravel migrations.

BEGIN {
    FS = "\t"
    in_copy = 0
    first_row = 1
    data_count = 0
    header_printed = 0
    insert_header = ""
}

function should_skip_table(t) {
    if (t == "migrations") return 1
    if (t == "jobs") return 1
    if (t == "job_batches") return 1
    if (t == "failed_jobs") return 1
    if (t == "cache") return 1
    if (t == "cache_locks") return 1
    if (t == "sessions") return 1
    return 0
}

function trim(s) {
    gsub(/^[[:space:]]+|[[:space:]]+$/, "", s)
    return s
}

function sql_value(v,    out) {
    if (v == "\\N") {
        return "NULL"
    }

    if (v == "t") {
        return "1"
    }

    if (v == "f") {
        return "0"
    }

    out = v
    gsub(/\\/, "\\\\", out)
    gsub(/'/, "''", out)
    return "'" out "'"
}

/^COPY public\./ {
    # Example:
    # COPY public.users (user_id, email, ...) FROM stdin;
    line = $0
    sub(/^COPY public\./, "", line)

    split(line, parts, " ")
    table = parts[1]

    if (should_skip_table(table)) {
        in_copy = 2
        next
    }

    in_copy = 1
    first_row = 1
    data_count = 0
    current_table = table

    open_paren = index(line, "(")
    close_paren = index(line, ")")
    cols = substr(line, open_paren + 1, close_paren - open_paren - 1)
    cols = trim(cols)

    insert_header = sprintf("INSERT INTO `%s` (%s) VALUES\n", table, cols)
    header_printed = 0
    next
}

in_copy && /^\\\.$/ {
    if (in_copy == 1 && data_count > 0) {
        printf(";\n\n")
    }
    in_copy = 0
    data_count = 0
    header_printed = 0
    next
}

in_copy && in_copy == 1 {
    row = ""
    for (i = 1; i <= NF; i++) {
        if (i > 1) {
            row = row ", "
        }
        row = row sql_value($i)
    }

    if (!header_printed) {
        printf("%s", insert_header)
        header_printed = 1
    }

    if (first_row) {
        printf("(%s)", row)
        first_row = 0
    } else {
        printf(",\n(%s)", row)
    }

    data_count++
    next
}

# Ignore all non-COPY lines.
{
    next
}
