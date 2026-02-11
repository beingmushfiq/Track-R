import React from 'react';
import { Loader2 } from 'lucide-react';
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

const Spinner = ({ size = 'md', className, ...props }) => {
  const sizes = {
    sm: 'w-4 h-4',
    md: 'w-6 h-6',
    lg: 'w-8 h-8',
    xl: 'w-12 h-12',
  };

  return (
    <Loader2 
      className={twMerge(
        clsx(
          "animate-spin text-indigo-600 dark:text-indigo-400",
          sizes[size],
          className
        )
      )}
      {...props}
    />
  );
};

const LoadingOverlay = ({ message = 'Loading...', className }) => {
  return (
    <div className={twMerge(
      clsx(
        "fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm",
        className
      )
    )}>
      <div className="bg-white dark:bg-slate-900 rounded-2xl p-8 shadow-2xl border border-slate-200 dark:border-slate-800">
        <div className="flex flex-col items-center gap-4">
          <Spinner size="xl" />
          <p className="text-slate-900 dark:text-white font-medium">{message}</p>
        </div>
      </div>
    </div>
  );
};

const LoadingCard = ({ message = 'Loading...', className }) => {
  return (
    <div className={twMerge(
      clsx(
        "flex flex-col items-center justify-center p-12 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800",
        className
      )
    )}>
      <Spinner size="lg" />
      <p className="mt-4 text-slate-600 dark:text-slate-400">{message}</p>
    </div>
  );
};

Spinner.Overlay = LoadingOverlay;
Spinner.Card = LoadingCard;

export default Spinner;
