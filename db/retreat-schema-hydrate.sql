-- ---------------------------------------------------------------------
-- Add data
-- ---------------------------------------------------------------------

INSERT INTO account_type (account_type_id, account_type_descr) VALUES (1, 'expense'), (2, 'asset');

INSERT INTO scenario (scenario_name, account_type_id, scenario_parent_id) values ('base', 1, null);
INSERT INTO scenario (scenario_name, account_type_id, scenario_parent_id) values ('alt', 1, 1);
INSERT INTO scenario (scenario_name, account_type_id, scenario_parent_id) values ('base', 2, null);
INSERT INTO scenario (scenario_name, account_type_id, scenario_parent_id) values ('alt', 2, 3);

INSERT INTO expense (
	scenario_id, expense_name, amount, inflation_rate, 
    begin_year, begin_month, end_year, end_month, 
    repeat_every
) VALUES (
	1, 'mortgage', 1500.00,
    2.225,
    2026, 1, 2999, 12,
    null
);

INSERT INTO expense (
	scenario_id, expense_name, amount, inflation_rate, 
    begin_year, begin_month, end_year, end_month, 
    repeat_every
) VALUES (
	1, 'groceries', 500.00,
    3.000,
    2026, 1, 2999, 12,
    null
);

INSERT INTO expense (
	scenario_id, expense_name, amount, inflation_rate, 
    begin_year, begin_month, end_year, end_month, 
    repeat_every
) VALUES (
	1, 'cell phone', 125.00,
    -2.000,
    2026, 1, 2999, 12,
    null
);

INSERT INTO expense (
	scenario_id, expense_name, amount, inflation_rate, 
    begin_year, begin_month, end_year, end_month, 
    repeat_every
) VALUES (
	1, 'property tax', 14000.00,
    3.000,
    2026, 1, 2999, 12,
    12
);

INSERT INTO expense (
	scenario_id, expense_name, amount
) VALUES (
	2, 'cell phone', 5.00
);

INSERT INTO asset (
	scenario_id, asset_name,
    opening_balance, max_withdrawal, apr,
    begin_after, begin_year, begin_month
) VALUES (
	3, 'savings_account', 
    1000.00, 510.00, 2.500,
    null, 2026, 1
);

INSERT INTO asset (
	scenario_id, asset_name,
    opening_balance, max_withdrawal, apr,
    begin_after, begin_year, begin_month
) VALUES (
	3, 'other savings_account', 
    200.00, 50.00, 1.000,
    0, null, null
);

INSERT INTO asset (
	scenario_id, asset_name,
    opening_balance, max_withdrawal, apr,
    begin_after, begin_year, begin_month
) VALUES (
	3, 'social security', 
    250000.00, 5000.00, 2.100,
    null, 2033, 2
);

INSERT INTO asset (
	scenario_id, asset_name,
    opening_balance, max_withdrawal, apr,
    begin_after, begin_year, begin_month
) VALUES (
	3, 'rsu1', 
    1000000.00, 500000.00, 3.015,
    null, 2026, 1
);

INSERT INTO asset (
	scenario_id, asset_name,
    opening_balance, max_withdrawal, apr,
    begin_after, begin_year, begin_month
) VALUES (
	3, 'rsu2', 
    500000.00, 500000.00, 3.015,
    null, 2026, 1
);

INSERT INTO asset (
	scenario_id, asset_name, max_withdrawal, apr
) VALUES (
	4, 'other savings account', 75.00, 1.5000
);

INSERT INTO asset (
	scenario_id, asset_name, begin_month
) VALUES (
	4, 'rsu2', 3
);

INSERT INTO asset (
	scenario_id, asset_name, max_withdrawal
) VALUES (
	4, 'rsu2', 17.50
);
