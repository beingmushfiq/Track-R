import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuthStore } from '../store/useAuthStore';
import Input from '../components/ui/Input';
import Button from '../components/ui/Button';
import { MapPin } from 'lucide-react';

const Login = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  
  const login = useAuthStore((state) => state.login);
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setIsLoading(true);

    const result = await login(email, password);
    
    if (result.success) {
      navigate('/dashboard');
    } else {
      setError(result.message);
    }
    setIsLoading(false);
  };

  return (
    <div className="p-8">
      <div className="flex flex-col items-center mb-8">
        <div className="w-12 h-12 bg-indigo-500/10 rounded-xl flex items-center justify-center mb-4">
          <MapPin className="w-6 h-6 text-indigo-500" />
        </div>
        <h1 className="text-2xl font-display font-bold text-slate-900 dark:text-white">Welcome Back</h1>
        <p className="text-slate-500 dark:text-slate-400 mt-2">Sign in to your Track-R account</p>
      </div>

      <form onSubmit={handleSubmit} className="space-y-6">
        <Input
          label="Email Address"
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          placeholder="admin@example.com"
          required
        />
        
        <div className="space-y-1">
          <Input
            label="Password"
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder="••••••••"
            required
          />
          <div className="text-right">
            <Link to="/forgot-password" class="text-xs font-medium text-indigo-500 hover:text-indigo-400">
              Forgot password?
            </Link>
          </div>
        </div>

        {error && (
          <div className="p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-sm text-red-500 text-center">
            {error}
          </div>
        )}

        <Button type="submit" className="w-full" isLoading={isLoading}>
          Sign In
        </Button>
      </form>

      <div className="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
        Don't have an account?{' '}
        <Link to="/register" className="font-medium text-indigo-500 hover:text-indigo-400">
          Create account
        </Link>
      </div>
    </div>
  );
};

export default Login;
