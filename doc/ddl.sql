create table themes (
    theme_id integer auto_increment,
    api_type text,
    cs_id text,
    cs_name text,
    colors text,
    share integer default 0,
    download_count integer default 0,
    ip_addr text,
    last_download_ip_addr text,
    created_at datetime,
    updated_at datetime,
    primary key (theme_id),
    index idx_theme_cs_id(cs_id(10))
) engine=InnoDB charset=utf8;

create table taggings (
    theme_id integer,
    tag_id integer,
    created_at datetime,
    primary key (tag_id, theme_id)
) engine=InnoDB charset=utf8;
