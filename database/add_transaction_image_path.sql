USE `finance_app`;

ALTER TABLE `transactions`
  ADD COLUMN `image_path` VARCHAR(255) NULL AFTER `transaction_date`;
