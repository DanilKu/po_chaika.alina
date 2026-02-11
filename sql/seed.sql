USE po_alina_warehouse;

-- Пользователи (пароли: useruser и adminadmin)
INSERT INTO users (login, password_hash, full_name, email, role) VALUES
('useruser', '$2y$12$Nds7Wm02IXwT.AgADbOvdeUVoWADunX.jyuJs7yjKQLtQCjrgFmvW', 'Иванов Иван', 'ivanov@example.com', 'user'),
('adminadmin', '$2y$12$SUnSOJvSX2e7izTlcYRfyuylliqDfwdb7djgXdEyMcCKXrWsVEFuC', 'Администратор Системы', 'admin@example.com', 'admin');

-- Категории
INSERT INTO categories (name, description) VALUES
('Канцтовары', 'Бумага, ручки, папки'),
('Электроника', 'Компьютеры и комплектующие'),
('Хозяйственные товары', 'Бытовая химия и расходники'),
('Офисная мебель', 'Столы, стулья, шкафы'),
('Продукты питания', 'Для кухни и перерывов');

-- Товары (часть с привязкой к категориям)
INSERT INTO products (category_id, name, sku, unit, price_buy, price_sell, quantity, min_quantity) VALUES
(1, 'Бумага А4 500 л', 'PAP-A4-500', 'пачка', 280.00, 420.00, 150, 20),
(1, 'Ручка шариковая синяя', 'PEN-BLUE', 'шт', 12.00, 25.00, 500, 100),
(1, 'Папка-регистратор А4', 'FOL-A4', 'шт', 85.00, 150.00, 80, 10),
(1, 'Степлер металлический', 'STAPLER-1', 'шт', 120.00, 220.00, 45, 5),
(1, 'Скрепки 28 мм', 'CLIP-28', 'коробка', 35.00, 65.00, 200, 30),
(2, 'Мышь офисная', 'MOUSE-OFF', 'шт', 350.00, 590.00, 60, 10),
(2, 'Клавиатура USB', 'KEYB-USB', 'шт', 420.00, 750.00, 40, 5),
(2, 'USB-флешка 32 ГБ', 'USB-32', 'шт', 280.00, 450.00, 120, 20),
(2, 'Сетевой фильтр 5 розеток', 'PWR-5', 'шт', 180.00, 320.00, 55, 8),
(2, 'Кабель HDMI 1.5 м', 'CAB-HDMI', 'шт', 150.00, 280.00, 90, 15),
(3, 'Мыло жидкое 1 л', 'SOAP-1L', 'шт', 95.00, 180.00, 70, 15),
(3, 'Бумажные полотенца', 'TOWEL-PAP', 'рулон', 45.00, 85.00, 200, 40),
(3, 'Мешки для мусора 30 л', 'BAG-30', 'уп', 120.00, 220.00, 80, 15),
(3, 'Салфетки офисные', 'NAPKIN-OFF', 'уп', 55.00, 95.00, 150, 25),
(4, 'Стул офисный', 'CHAIR-OFF', 'шт', 3200.00, 5200.00, 25, 3),
(4, 'Стол письменный', 'DESK-1', 'шт', 5800.00, 9200.00, 12, 2),
(4, 'Шкаф для документов', 'CAB-DOC', 'шт', 4100.00, 6800.00, 8, 1),
(5, 'Чай черный пакетированный', 'TEA-BLACK', 'уп', 65.00, 120.00, 100, 20),
(5, 'Кофе молотый 250 г', 'COFFEE-250', 'шт', 220.00, 380.00, 45, 10),
(5, 'Сахар 1 кг', 'SUGAR-1', 'пачка', 55.00, 95.00, 60, 15);

-- Клиенты
INSERT INTO clients (name, phone, email, address) VALUES
('ООО "Ромашка"', '+7 (495) 111-22-33', 'romashka@mail.ru', 'г. Москва, ул. Ленина, 1'),
('ИП Петров П.П.', '+7 (495) 222-33-44', 'petrov@yandex.ru', 'г. Москва, пр. Мира, 15'),
('ЗАО "Вектор"', '+7 (495) 333-44-55', 'vector@company.ru', 'г. Москва, ул. Тверская, 10'),
('ООО "Стройсервис"', '+7 (495) 444-55-66', 'stroi@mail.ru', 'г. Москва, ул. Строителей, 5'),
('Магазин "Канцмир"', '+7 (495) 555-66-77', 'kancmir@shop.ru', 'г. Москва, ул. Торговая, 22'),
('Физлицо Сидоров', '+7 (916) 123-45-67', NULL, NULL);

-- Заказы (за прошлые месяцы)
INSERT INTO orders (client_id, user_id, status, total, comment, created_at) VALUES
(1, 1, 'completed', 12580.00, 'Срочная доставка', DATE_SUB(NOW(), INTERVAL 25 DAY)),
(2, 1, 'completed', 3420.00, NULL, DATE_SUB(NOW(), INTERVAL 20 DAY)),
(3, 2, 'completed', 18900.00, NULL, DATE_SUB(NOW(), INTERVAL 15 DAY)),
(1, 1, 'completed', 8560.00, NULL, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(4, 1, 'shipped', 22400.00, NULL, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(5, 2, 'confirmed', 15800.00, 'Ожидание оплаты', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 1, 'draft', 0, 'Черновик', NOW());

-- Позиции заказов
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 20, 420.00),
(1, 2, 100, 25.00),
(1, 6, 10, 590.00),
(2, 3, 15, 150.00),
(2, 8, 5, 450.00),
(3, 15, 3, 5200.00),
(3, 16, 1, 9200.00),
(4, 1, 10, 420.00),
(4, 7, 8, 750.00),
(5, 15, 4, 5200.00),
(5, 16, 1, 9200.00),
(6, 1, 15, 420.00),
(6, 14, 50, 95.00),
(6, 4, 20, 220.00);

-- Движения по складу (история приходов/расходов)
INSERT INTO stock_movements (product_id, type, quantity, balance_after, user_id, comment, created_at) VALUES
(1, 'in', 200, 200, 2, 'Первоначальное поступление', DATE_SUB(NOW(), INTERVAL 60 DAY)),
(2, 'in', 600, 600, 2, 'Поставка от ООО Канцтовары', DATE_SUB(NOW(), INTERVAL 55 DAY)),
(1, 'out', 50, 150, 1, 'Отгрузка по заказу №1', DATE_SUB(NOW(), INTERVAL 25 DAY)),
(2, 'out', 100, 500, 1, 'Отгрузка по заказу №1', DATE_SUB(NOW(), INTERVAL 25 DAY)),
(6, 'in', 70, 70, 2, 'Поступление электроники', DATE_SUB(NOW(), INTERVAL 30 DAY)),
(6, 'out', 10, 60, 1, 'Заказ №1', DATE_SUB(NOW(), INTERVAL 25 DAY)),
(15, 'in', 30, 30, 2, 'Мебель', DATE_SUB(NOW(), INTERVAL 40 DAY)),
(15, 'out', 3, 27, 2, 'Заказ №3', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(15, 'out', 1, 26, 1, 'Заказ №3', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(1, 'in', 50, 200, 2, 'Дозаказ бумаги', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(1, 'out', 50, 150, 1, 'Заказ №4', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(7, 'out', 8, 40, 1, 'Заказ №4', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(15, 'out', 4, 25, 1, 'Заказ №5', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(16, 'out', 1, 12, 1, 'Заказ №5', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 'adjust', -5, 145, 2, 'Инвентаризация: недостача', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 'in', 10, 155, 2, 'Корректировка после инвентаризации', DATE_SUB(NOW(), INTERVAL 2 DAY));

-- Обновить итоги заказов (total)
UPDATE orders o SET total = (SELECT COALESCE(SUM(quantity * price), 0) FROM order_items WHERE order_id = o.id);
