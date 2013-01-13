create table theme (
    theme_id integer auto_increment,
    api_type text,
    cs_id text,
    colors text,
    created_at datetime,
    updated_at datetime,
    primary key (theme_id),
    index idx_theme_cs_id(cs_id(10))
) engine=InnoDB charset=utf8;
