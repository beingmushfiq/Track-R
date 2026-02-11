import React, { useEffect } from 'react';
import { X, CheckCircle, AlertCircle, Info, AlertTriangle } from 'lucide-react';
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

const Alert = ({ 
  type = 'info', 
  title, 
  message, 
  onClose,
  dismissible = true,
  className 
}) => {
  const types = {
    success: {
      bg: 'bg-emerald-50 dark:bg-emerald-500/10',
      border: 'border-emerald-200 dark:border-emerald-800',
      text: 'text-emerald-800 dark:text-emerald-400',
      icon: CheckCircle,
    },
    error: {
      bg: 'bg-red-50 dark:bg-red-500/10',
      border: 'border-red-200 dark:border-red-800',
      text: 'text-red-800 dark:text-red-400',
      icon: AlertCircle,
    },
    warning: {
      bg: 'bg-orange-50 dark:bg-orange-500/10',
      border: 'border-orange-200 dark:border-orange-800',
      text: 'text-orange-800 dark:text-orange-400',
      icon: AlertTriangle,
    },
    info: {
      bg: 'bg-blue-50 dark:bg-blue-500/10',
      border: 'border-blue-200 dark:border-blue-800',
      text: 'text-blue-800 dark:text-blue-400',
      icon: Info,
    },
  };

  const config = types[type] || types.info;
  const Icon = config.icon;

  return (
    <div
      className={twMerge(
        clsx(
          "flex items-start gap-3 p-4 rounded-lg border",
          config.bg,
          config.border,
          className
        )
      )}
    >
      <Icon className={twMerge(clsx("w-5 h-5 flex-shrink-0 mt-0.5", config.text))} />
      
      <div className="flex-1">
        {title && (
          <h4 className={twMerge(clsx("font-semibold mb-1", config.text))}>
            {title}
          </h4>
        )}
        {message && (
          <p className={twMerge(clsx("text-sm", config.text))}>
            {message}
          </p>
        )}
      </div>

      {dismissible && onClose && (
        <button
          onClick={onClose}
          className={twMerge(
            clsx(
              "flex-shrink-0 p-1 rounded hover:bg-black/5 dark:hover:bg-white/5 transition-colors",
              config.text
            )
          )}
        >
          <X className="w-4 h-4" />
        </button>
      )}
    </div>
  );
};

// Toast notification component
const Toast = ({ 
  type = 'info', 
  message, 
  duration = 3000,
  onClose,
  position = 'top-right'
}) => {
  useEffect(() => {
    if (duration && onClose) {
      const timer = setTimeout(onClose, duration);
      return () => clearTimeout(timer);
    }
  }, [duration, onClose]);

  const positions = {
    'top-right': 'top-4 right-4',
    'top-left': 'top-4 left-4',
    'bottom-right': 'bottom-4 right-4',
    'bottom-left': 'bottom-4 left-4',
    'top-center': 'top-4 left-1/2 -translate-x-1/2',
    'bottom-center': 'bottom-4 left-1/2 -translate-x-1/2',
  };

  return (
    <div className={twMerge(clsx("fixed z-50 animate-in slide-in-from-top-2 fade-in duration-300", positions[position]))}>
      <Alert 
        type={type} 
        message={message} 
        onClose={onClose}
        className="min-w-[300px] shadow-lg"
      />
    </div>
  );
};

Alert.Toast = Toast;

export default Alert;
