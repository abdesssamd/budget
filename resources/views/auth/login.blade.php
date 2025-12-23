<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>G-STOCK | Connexion</title>

    <!-- Google Font: Cairo -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AdminLTE & Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-image: url('https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1920&auto=format&fit=crop'); 
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Effet de verre fumé (Glassmorphism) sur le fond */
        .login-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(44, 62, 80, 0.7); /* Couleur sombre transparente */
            backdrop-filter: blur(5px);
            z-index: 0;
        }

        .login-box {
            width: 400px;
            z-index: 1; /* Au-dessus du flou */
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .card-header {
            background: #fff;
            border-bottom: none;
            padding-top: 30px;
            text-align: center;
        }

        .login-logo b {
            color: #2c3e50;
            font-weight: 700;
            font-size: 2.2rem;
        }
        
        .login-logo span {
            color: #3498db;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border: none;
            border-radius: 50px;
            font-weight: bold;
            padding: 10px 20px;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        .form-control {
            border-radius: 50px;
            padding: 20px 20px;
            border: 1px solid #eee;
            background-color: #f8f9fa;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #3498db;
            background-color: #fff;
        }

        .input-group-text {
            border-radius: 50px;
            border: none;
            background: transparent;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            color: #aaa;
        }
        
        .input-group { position: relative; }
    </style>
</head>
<body>

    <div class="login-overlay"></div>

    <div class="login-box">
        <div class="card">
            <div class="card-header text-center">
                <a href="#" class="h1 login-logo"><b>G-Stock</b> <span>Budget</span></a>
                <p class="login-box-msg text-muted mt-2">Connectez-vous pour gérer vos finances</p>
            </div>
            
            <div class="card-body login-card-body p-4">
                <form action="{{ route('login') }}" method="post">
                    @csrf

                    <!-- Email -->
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="email" name="email" class="form-control" placeholder="Email" required autofocus>
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                        @error('email')
                            <span class="text-danger small pl-3">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Mot de passe -->
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        @error('password')
                            <span class="text-danger small pl-3">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Options -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember" style="font-weight: normal; font-size: 0.9rem; cursor: pointer;">
                                    Se souvenir de moi
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-sign-in-alt mr-2"></i> CONNEXION
                            </button>
                        </div>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <p class="mb-1">
                        <a href="{{ route('password.request') }}" class="text-muted small">Mot de passe oublié ?</a>
                    </p>
                </div>
            </div>
            <div class="card-footer bg-light text-center py-3">
                <small class="text-muted">Système de Gestion Budgétaire v1.0</small>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>