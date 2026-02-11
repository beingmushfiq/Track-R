import React from 'react';
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

const Card = ({ className, children, hover = false, ...props }) => {
  return (
    <div
      className={twMerge(
        clsx(
          "bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all duration-200",
          hover && "hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700",
          className
        )
      )}
      {...props}
    >
      {children}
    </div>
  );
};

const CardHeader = ({ className, children, ...props }) => {
  return (
    <div
      className={twMerge(
        clsx(
          "px-6 py-4 border-b border-slate-100 dark:border-slate-800",
          className
        )
      )}
      {...props}
    >
      {children}
    </div>
  );
};

const CardBody = ({ className, children, ...props }) => {
  return (
    <div
      className={twMerge(clsx("p-6", className))}
      {...props}
    >
      {children}
    </div>
  );
};

const CardFooter = ({ className, children, ...props }) => {
  return (
    <div
      className={twMerge(
        clsx(
          "px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 rounded-b-2xl",
          className
        )
      )}
      {...props}
    >
      {children}
    </div>
  );
};

Card.Header = CardHeader;
Card.Body = CardBody;
Card.Footer = CardFooter;

export default Card;
