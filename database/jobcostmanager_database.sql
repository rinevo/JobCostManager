-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- ホスト: localhost
-- 生成時間: 2012 年 12 月 04 日 14:13
-- サーバのバージョン: 5.5.16
-- PHP のバージョン: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- データベース: `jobcostmanager`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_costfield`
--

CREATE TABLE IF NOT EXISTS `jc_costfield` (
  `id` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `sortno` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- テーブルのデータをダンプしています `jc_costfield`
--

INSERT INTO `jc_costfield` (`id`, `name`, `sortno`) VALUES
('project_no', 'プロジェクト', 1),
('work_no', '作業', 2),
('customer_no', '顧客', 3),
('process_no', '工程', 4),
('action', '行動', 5);

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_customer`
--

CREATE TABLE IF NOT EXISTS `jc_customer` (
  `no` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sortno` int(11) NOT NULL,
  `group_no` int(11) NOT NULL,
  UNIQUE KEY `no` (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_group`
--

CREATE TABLE IF NOT EXISTS `jc_group` (
  `no` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `flg` int(1) NOT NULL,
  UNIQUE KEY `no` (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_group_member`
--

CREATE TABLE IF NOT EXISTS `jc_group_member` (
  `group_no` int(11) NOT NULL,
  `uid` varchar(50) NOT NULL,
  `role_no` int(11) NOT NULL,
  `status` int(1) NOT NULL,
  UNIQUE KEY `group_no` (`group_no`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_ini`
--

CREATE TABLE IF NOT EXISTS `jc_ini` (
  `no` int(11) NOT NULL,
  `inino` int(11) NOT NULL,
  `inidata` varchar(255) NOT NULL,
  `group_no` int(11) NOT NULL,
  `uid` varchar(50) NOT NULL,
  UNIQUE KEY `no` (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_kintai`
--

CREATE TABLE IF NOT EXISTS `jc_kintai` (
  `no` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `flg` int(11) NOT NULL,
  `total_flg` int(1) NOT NULL,
  UNIQUE KEY `no` (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- テーブルのデータをダンプしています `jc_kintai`
--

INSERT INTO `jc_kintai` (`no`, `name`, `flg`, `total_flg`) VALUES
(1, '出勤', 1, 1),
(2, '有休', 1, 1),
(3, '半休', 3, 1),
(4, '時休', 3, 2),
(5, '欠勤', 1, 1),
(6, '遅刻', 1, 1),
(7, '休日', 1, 1),
(8, '早退', 2, 1),
(9, '退勤', 2, 1);

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_lock`
--

CREATE TABLE IF NOT EXISTS `jc_lock` (
  `group_no` int(11) NOT NULL,
  `data` int(11) NOT NULL,
  `data_no` varchar(50) NOT NULL,
  `uid` varchar(50) NOT NULL,
  `time` datetime NOT NULL,
  UNIQUE KEY `group_no` (`group_no`,`data`,`data_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_log`
--

CREATE TABLE IF NOT EXISTS `jc_log` (
  `time` datetime NOT NULL,
  `status` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `func` varchar(255) NOT NULL,
  `line` varchar(255) NOT NULL,
  `uid` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `agent` varchar(255) NOT NULL,
  `lang` varchar(255) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `request_method` varchar(255) NOT NULL,
  `request_value` text NOT NULL,
  `session` text NOT NULL,
  `cookie` varchar(255) NOT NULL,
  `group_no` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_period`
--

CREATE TABLE IF NOT EXISTS `jc_period` (
  `no` int(1) NOT NULL,
  `name` varchar(10) NOT NULL,
  `sortno` int(1) NOT NULL,
  UNIQUE KEY `no` (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- テーブルのデータをダンプしています `jc_period`
--

INSERT INTO `jc_period` (`no`, `name`, `sortno`) VALUES
(1, '年間', 1),
(2, '月間', 2),
(3, '週間', 3),
(4, '日間', 4);

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_process`
--

CREATE TABLE IF NOT EXISTS `jc_process` (
  `no` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sortno` int(11) NOT NULL,
  `group_no` int(11) NOT NULL,
  UNIQUE KEY `no` (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- テーブルのデータをダンプしています `jc_process`
--

INSERT INTO `jc_process` (`no`, `name`, `sortno`, `group_no`) VALUES
(1, '調査・研究', 1, 2),
(2, '企画・提案', 2, 2),
(3, '要求定義', 3, 2),
(4, '見積', 4, 2),
(5, '設計', 5, 2),
(6, 'PG', 6, 2),
(7, 'テスト', 7, 2),
(8, 'マニュアル作成', 8, 2),
(9, '出荷', 9, 2),
(10, '導入', 10, 2),
(11, '運用・保守', 11, 2),
(12, '会議', 12, 2),
(13, '雑務', 13, 2);

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_project`
--

CREATE TABLE IF NOT EXISTS `jc_project` (
  `no` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sortno` int(11) NOT NULL,
  `group_no` int(11) NOT NULL,
  UNIQUE KEY `no` (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_role`
--

CREATE TABLE IF NOT EXISTS `jc_role` (
  `group_no` int(11) NOT NULL,
  `no` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `sortno` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `work` int(11) NOT NULL,
  `customer` int(11) NOT NULL,
  `process` int(11) NOT NULL,
  `todo` int(11) NOT NULL,
  `schedule` int(11) NOT NULL,
  `cost` int(11) NOT NULL,
  `kintai` int(11) NOT NULL,
  UNIQUE KEY `group_no` (`group_no`,`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_schedule`
--

CREATE TABLE IF NOT EXISTS `jc_schedule` (
  `no` int(11) NOT NULL,
  `date` date NOT NULL,
  `sortno` int(11) NOT NULL,
  `time` time DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `costin` float NOT NULL,
  `costout` float NOT NULL,
  `section_no` int(11) NOT NULL,
  `kintai_no` int(11) NOT NULL,
  `project_no` int(11) NOT NULL,
  `work_no` int(11) NOT NULL,
  `process_no` int(11) NOT NULL,
  `customer_no` int(11) NOT NULL,
  `uid` varchar(50) NOT NULL,
  `group_no` int(11) NOT NULL,
  UNIQUE KEY `no` (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_section`
--

CREATE TABLE IF NOT EXISTS `jc_section` (
  `no` int(11) NOT NULL,
  `name` varchar(8) NOT NULL,
  UNIQUE KEY `no` (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- テーブルのデータをダンプしています `jc_section`
--

INSERT INTO `jc_section` (`no`, `name`) VALUES
(1, '出勤'),
(2, '行動'),
(3, '退勤');

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_status`
--

CREATE TABLE IF NOT EXISTS `jc_status` (
  `no` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `sortno` int(11) NOT NULL,
  UNIQUE KEY `no` (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- テーブルのデータをダンプしています `jc_status`
--

INSERT INTO `jc_status` (`no`, `name`, `sortno`) VALUES
(0, '未着手', 1),
(1, '作業中', 2),
(2, '終了', 3),
(3, '保留', 4),
(4, '中止', 5);

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_todo`
--

CREATE TABLE IF NOT EXISTS `jc_todo` (
  `no` int(11) NOT NULL,
  `sortno` int(11) NOT NULL,
  `project_no` int(11) NOT NULL,
  `work_no` int(11) NOT NULL,
  `customer_no` int(11) NOT NULL,
  `process_no` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `status_no` int(11) NOT NULL,
  `uid` varchar(50) NOT NULL,
  `group_no` int(11) NOT NULL,
  UNIQUE KEY `no` (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_todofield`
--

CREATE TABLE IF NOT EXISTS `jc_todofield` (
  `sort_id` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `sortno` int(11) NOT NULL,
  `show_id` varchar(50) NOT NULL,
  UNIQUE KEY `sort_id` (`sort_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- テーブルのデータをダンプしています `jc_todofield`
--

INSERT INTO `jc_todofield` (`sort_id`, `name`, `sortno`, `show_id`) VALUES
('action', '行動', 7, 'action'),
('costin', '時間内', 9, 'costin'),
('costout', '時間外', 10, 'costout'),
('customer_sortno', '顧客', 6, 'customer_name'),
('date_end', '終了日', 5, 'date_end'),
('date_top', '開始日', 4, 'date_top'),
('process_sortno', '工程', 8, 'process_name'),
('project_sortno', 'プロジェクト', 1, 'project_name'),
('status_sortno', '状況', 3, 'status_name'),
('work_sortno', '作業', 2, 'work_name');

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_user`
--

CREATE TABLE IF NOT EXISTS `jc_user` (
  `uid` varchar(50) NOT NULL,
  `passwd` char(50) NOT NULL,
  `mail` varchar(100) NOT NULL,
  `status` int(1) NOT NULL,
  `param` varchar(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  `update_time` datetime NOT NULL,
  `update_uid` varchar(50) NOT NULL,
  `group_no_default` int(1) NOT NULL,
  UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- テーブルの構造 `jc_work`
--

CREATE TABLE IF NOT EXISTS `jc_work` (
  `no` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sortno` int(11) NOT NULL,
  `project_no` int(11) NOT NULL,
  `group_no` int(11) NOT NULL,
  UNIQUE KEY `no` (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
