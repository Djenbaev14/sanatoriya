<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bemor Yotqizish Ma'lumotlari</title>
    <style>

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header h1 {
            color: #2d3748;
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header p {
            text-align: center;
            color: #718096;
            font-size: 1.1rem;
        }

        .main-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 2px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .info-card {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 15px;
            padding: 25px;
            border-left: 5px solid;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.8), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .patient-card { border-left-color: #48bb78; }
        .user-card { border-left-color: #4299e1; }
        .history-card { border-left-color: #ed8936; }
        .payment-card { border-left-color: #9f7aea; }
        .room-card { border-left-color: #38b2ac; }
        .bed-card { border-left-color: #ec407a; }
        .tariff-card { border-left-color: #5a67d8; }
        .price-card { border-left-color: #f56565; }
        .meal-card { border-left-color: #38a169; }
        .meal-price-card { border-left-color: #d69e2e; }
        .date-card { border-left-color: #805ad5; }
        .discharge-card { border-left-color: #3182ce; }

        .info-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-value {
            font-size: 1.125rem;
            font-weight: 600;
            color: #2d3748;
            word-break: break-word;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 16px;
            border-radius: 25px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-success {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
        }

        .badge-warning {
            background: linear-gradient(135deg, #ed8936, #d69e2e);
            color: white;
        }

        .badge-danger {
            background: linear-gradient(135deg, #f56565, #e53e3e);
            color: white;
        }

        .calculation-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 35px;
            margin-top: 40px;
            color: white;
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
        }

        .calculation-title {
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 25px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .calculation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .calculation-item {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .calculation-item:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(1.02);
        }

        .calculation-label {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .calculation-value {
            font-size: 1.25rem;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .total-amount {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .total-label {
            font-size: 1.125rem;
            opacity: 0.9;
            margin-bottom: 10px;
        }

        .total-value {
            font-size: 2.5rem;
            font-weight: 800;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            background: rgba(255, 255, 255, 0.2);
            padding: 15px 25px;
            border-radius: 10px;
            display: inline-block;
        }

        .icon {
            width: 16px;
            height: 16px;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .calculation-grid {
                grid-template-columns: 1fr;
            }
            
            .total-value {
                font-size: 2rem;
            }
        }

        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body>

            <div class="calculation-section">
                <h3 class="calculation-title">💰 Hisob-kitob</h3>
                
                <div class="calculation-grid">
                    <div class="calculation-item">
                        <div class="calculation-label">
                            <span>🗓️</span>
                            Kunlar soni
                        </div>
                        <div class="calculation-value">8 kun</div>
                    </div>

                    <div class="calculation-item">
                        <div class="calculation-label">
                            <span>🛏️</span>
                            Koyka narxi
                        </div>
                        <div class="calculation-value">350 000 so'm</div>
                    </div>

                    <div class="calculation-item">
                        <div class="calculation-label">
                            <span>🍽️</span>
                            Ovqat narxi
                        </div>
                        <div class="calculation-value">75 000 so'm</div>
                    </div>

                    <div class="calculation-item">
                        <div class="calculation-label">
                            <span>🛏️</span>
                            Koyka uchun
                        </div>
                        <div class="calculation-value">2 800 000 so'm</div>
                    </div>

                    <div class="calculation-item">
                        <div class="calculation-label">
                            <span>🍽️</span>
                            Ovqat uchun
                        </div>
                        <div class="calculation-value">600 000 so'm</div>
                    </div>
                </div>

                <div class="total-amount">
                    <div class="total-label">💵 Umumiy summa</div>
                    <div class="total-value">3 400 000 so'm</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to info cards
            const infoCards = document.querySelectorAll('.info-card');
            infoCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Add click animation to calculation items
            const calculationItems = document.querySelectorAll('.calculation-item');
            calculationItems.forEach(item => {
                item.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1.02)';
                    }, 150);
                });
            });

            // Animate numbers on load
            const numberElements = document.querySelectorAll('.calculation-value, .total-value');
            numberElements.forEach(element => {
                const text = element.textContent;
                element.style.opacity = '0';
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transition = 'opacity 0.5s ease';
                }, Math.random() * 1000);
            });
        });
    </script>
</body>
</html>