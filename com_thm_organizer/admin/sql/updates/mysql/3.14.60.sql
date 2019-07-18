CREATE TABLE `#__thm_organizer_holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_de` varchar(50) NOT NULL,
  `name_en` varchar(50) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `type` tinyint(1) NOT NULL
       COMMENT 'Type of Holiday in deciding the Planning Schedule. Possible values: 1 - Automatic, 2 - Manual, 3 - Unplannable.',
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `#__thm_organizer_holidays` (`id`, `name_de`, `name_en`, `startDate`, `endDate`, `type`) VALUES
(1, 'Christi Himmelfahrt', 'Ascension Day', '2019-05-30', '2019-05-30', 3),
(2, 'Weihnachten', 'Christmas Day ', '2019-12-25', '2019-12-26', 3),
(3, 'Tag der Deutschen Einheit', 'Day of German Unity', '2019-10-03', '2019-10-03', 3),
(4, 'Ostermontag', 'Easter Monday', '2019-04-22', '2019-04-22', 3),
(5, 'Karfreitag', 'Good Friday', '2019-04-19', '2019-04-19', 3),
(6, 'Tag der Arbeit', 'May Day', '2019-05-01', '2019-05-01', 3),
(7, 'Neujahrstag', 'New Year\'s Day', '2019-01-01', '2019-01-01', 3),
(8, 'Pfingstmontag', 'Whit Monday', '2019-06-10', '2019-06-10', 3),
(9, 'Fronleichnam', 'Corpus Christi', '2019-06-20', '2019-06-20', 3),
(10, 'Neujahrstag', 'New Year\'s Day', '2020-01-01', '2020-01-01', 3),
(11, 'Karfreitag', 'Good Friday', '2020-04-10', '2020-04-10', 3),
(12, 'Ostermontag', 'Easter Monday', '2020-04-13', '2020-04-13', 3),
(13, 'Tag der Arbeit', 'May Day', '2020-05-01', '2020-05-01', 3),
(14, 'Christi Himmelfahrt', 'Ascension Day', '2020-05-21', '2020-05-21', 3),
(15, 'Pfingstmontag', 'Whit Monday', '2020-06-01', '2020-06-01', 3),
(16, 'Fronleichnam', 'Corpus Christi', '2020-06-11', '2020-06-11', 3),
(17, 'Tag der Deutschen Einheit', 'Day of German Unity', '2020-10-03', '2020-10-03', 3),
(18, 'Weihnachten', 'Christmas Day', '2020-12-25', '2020-12-27', 3),
(19, 'Neujahrstag', 'New Year\'s Day', '2021-01-01', '2021-01-01', 3),
(20, 'Karfreitag', 'Good Friday', '2021-04-02', '2021-04-02', 3),
(21, 'Ostermontag', 'Easter Monday', '2021-04-05', '2021-04-05', 3),
(22, 'Tag der Arbeit', 'May Day', '2021-05-01', '2021-05-01', 3),
(23, 'Christi Himmelfahrt', 'Ascension Day', '2021-05-13', '2021-05-13', 3),
(24, 'Pfingstmontag', 'Whit Monday', '2021-05-24', '2021-05-24', 3),
(25, 'Fronleichnam', 'Corpus Christi', '2021-06-03', '2021-06-03', 3),
(26, 'Weihnachten', 'Christmas Day', '2021-12-25', '2021-12-26', 3),
(27, 'Tag der Deutschen Einheit', 'Day of German Unity', '2021-10-03', '2021-10-03', 3),
(28, 'Tag der Deutschen Einheit', 'Day of German Unity', '2022-10-03', '2022-10-03', 3),
(29, 'Neujahrstag', 'New Year\'s Day', '2022-01-01', '2022-01-01', 3),
(30, 'Karfreitag', 'Good Friday', '2022-04-15', '2022-04-15', 3),
(31, 'Ostermontag', 'Easter Monday', '2022-04-18', '2022-04-18', 3),
(32, 'Tag der Arbeit', 'May Day', '2022-05-01', '2022-05-01', 3),
(33, 'Christi Himmelfahrt', 'Ascension Day', '2022-05-26', '2022-05-26', 3),
(34, 'Pfingstmontag', 'Whit Monday', '2022-06-06', '2022-06-06', 3),
(35, 'Fronleichnam', 'Corpus Christi', '2022-06-16', '2022-06-16', 3),
(36, 'Weihnachten', 'Christmas Day', '2022-12-25', '2022-12-26', 3),
(37, 'Neujahrstag', 'New Year\'s Day', '2023-01-01', '2023-01-01', 3),
(38, 'Karfreitag', 'Good Friday', '2023-04-07', '2023-04-07', 3),
(39, 'Ostermontag', 'Easter Monday', '2023-04-10', '2023-04-10', 3),
(40, 'Tag der Arbeit', 'May Day', '2023-05-01', '2023-05-01', 3),
(41, 'Christi Himmelfahrt', 'Ascension Day', '2023-05-18', '2023-05-18', 3),
(42, 'Pfingstmontag', 'Whit Monday', '2023-05-29', '2023-05-29', 3),
(43, 'Fronleichnam', 'Corpus Christi', '2023-06-08', '2023-06-08', 3),
(44, 'Tag der Deutschen Einheit', 'Day of German Unity', '2023-10-03', '2023-10-03', 3),
(45, 'Weihnachten', 'Christmas Day', '2023-12-25', '2023-12-26', 3),
(46, 'Neujahrstag', 'New Year\'s Day', '2024-01-01', '2024-01-01', 3),
(47, 'Karfreitag', 'Good Friday', '2024-03-29', '2024-03-29', 3),
(48, 'Ostermontag', 'Easter Monday', '2024-04-01', '2024-04-01', 3),
(49, 'Tag der Arbeit', 'May Day', '2024-05-01', '2024-05-01', 3),
(50, 'Christi Himmelfahrt', 'Ascension Day', '2024-05-09', '2024-05-09', 3),
(51, 'Pfingstmontag', 'Whit Monday', '2024-05-20', '2024-05-20', 3),
(52, 'Fronleichnam', 'Corpus Christi', '2024-05-30', '2024-05-30', 3),
(53, 'Tag der Deutschen Einheit', 'Day of German Unity', '2024-10-03', '2024-10-03', 3),
(54, 'Weihnachten', 'Christmas Day', '2024-12-25', '2024-12-26', 3);