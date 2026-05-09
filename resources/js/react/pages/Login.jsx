import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '../components/ui/Card';
import { Button } from '../components/ui/Button';
import { Input } from '../components/ui/Input';
import { Mail, Lock, ChefHat } from 'lucide-react';

export default function Login({ csrfToken, loginRoute, oldEmail = '', errorMessage = '' }) {
    const [isLoading, setIsLoading] = useState(false);

    // Mantenemos la sumisión nativa del formulario hacia el controlador de Laravel
    return (
        <div className="min-h-screen bg-slate-50 flex items-center justify-center p-4 font-sans">
            <Card className="w-full max-w-md shadow-xl border-0 ring-1 ring-slate-200/50">
                <CardHeader className="space-y-2 text-center bg-indigo-600 text-white rounded-t-xl pb-8 pt-10">
                    <div className="flex justify-center mb-4">
                        <div className="p-3 bg-white/20 rounded-full">
                            <ChefHat className="w-10 h-10 text-white" />
                        </div>
                    </div>
                    <CardTitle className="text-2xl font-bold tracking-tight">Restaurante POS</CardTitle>
                    <CardDescription className="text-indigo-100 font-medium">
                        Sistema de Gestión Profesional
                    </CardDescription>
                </CardHeader>
                <CardContent className="pt-8">
                    <div className="text-center mb-6">
                        <h3 className="text-lg font-semibold text-slate-800">Bienvenido de nuevo</h3>
                        <p className="text-sm text-slate-500">Ingresa tus credenciales para continuar</p>
                    </div>

                    <form action={loginRoute} method="POST" onSubmit={() => setIsLoading(true)} className="space-y-5">
                        <input type="hidden" name="_token" value={csrfToken} />

                        <div className="space-y-1.5 text-left">
                            <label htmlFor="email" className="text-sm font-semibold text-slate-700">
                                Correo Electrónico
                            </label>
                            <Input
                                id="email"
                                name="email"
                                type="email"
                                defaultValue={oldEmail}
                                placeholder="admin@admin.com"
                                required
                                autoFocus
                                leftIcon={<Mail className="h-4 w-4" />}
                                error={errorMessage}
                            />
                        </div>

                        <div className="space-y-1.5 text-left">
                            <label htmlFor="password" className="text-sm font-semibold text-slate-700">
                                Contraseña
                            </label>
                            <Input
                                id="password"
                                name="password"
                                type="password"
                                placeholder="••••••••"
                                required
                                leftIcon={<Lock className="h-4 w-4" />}
                            />
                        </div>

                        <Button 
                            type="submit" 
                            className="w-full h-11 text-base shadow-md mt-2"
                            isLoading={isLoading}
                        >
                            INGRESAR AL SISTEMA
                        </Button>
                    </form>
                </CardContent>
                <CardFooter className="justify-center border-t border-slate-100 pt-4 pb-6">
                    <p className="text-xs text-slate-400 font-medium">
                        Desarrollado con Laravel, React & Tailwind CSS
                    </p>
                </CardFooter>
            </Card>
        </div>
    );
}
