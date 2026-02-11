import React, { useState, useRef, useEffect } from 'react';
import { ChevronDown, Check } from 'lucide-react';
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

const Select = ({ 
  label, 
  options = [], 
  value, 
  onChange, 
  placeholder = 'Select an option',
  error,
  className,
  ...props 
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const selectRef = useRef(null);

  const selectedOption = options.find(opt => opt.value === value);

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (selectRef.current && !selectRef.current.contains(event.target)) {
        setIsOpen(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleSelect = (optionValue) => {
    onChange?.(optionValue);
    setIsOpen(false);
  };

  return (
    <div className={twMerge(clsx("w-full", className))}>
      {label && (
        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
          {label}
        </label>
      )}
      
      <div ref={selectRef} className="relative">
        <button
          type="button"
          onClick={() => setIsOpen(!isOpen)}
          className={twMerge(
            clsx(
              "w-full px-4 py-2.5 bg-white dark:bg-slate-900 border rounded-lg text-left flex items-center justify-between transition-all",
              "focus:outline-none focus:ring-2 focus:ring-indigo-500/50",
              error 
                ? "border-red-300 dark:border-red-800" 
                : "border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700",
              !selectedOption && "text-slate-400"
            )
          )}
          {...props}
        >
          <span className="text-slate-900 dark:text-white">
            {selectedOption ? selectedOption.label : placeholder}
          </span>
          <ChevronDown 
            className={twMerge(
              clsx(
                "w-5 h-5 text-slate-400 transition-transform",
                isOpen && "rotate-180"
              )
            )} 
          />
        </button>

        {isOpen && (
          <div className="absolute z-50 w-full mt-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg shadow-lg max-h-60 overflow-auto">
            {options.map((option) => (
              <button
                key={option.value}
                type="button"
                onClick={() => handleSelect(option.value)}
                className={twMerge(
                  clsx(
                    "w-full px-4 py-2.5 text-left flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors",
                    value === option.value && "bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400"
                  )
                )}
              >
                <span className="text-slate-900 dark:text-white">{option.label}</span>
                {value === option.value && (
                  <Check className="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                )}
              </button>
            ))}
          </div>
        )}
      </div>

      {error && (
        <p className="mt-1 text-sm text-red-600 dark:text-red-400">{error}</p>
      )}
    </div>
  );
};

export default Select;
