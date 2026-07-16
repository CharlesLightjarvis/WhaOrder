import { MessageCircle } from 'lucide-react';
import React from 'react';

interface LogoProps {
    className?: string;
    style?: React.CSSProperties;
}

const Logo: React.FC<LogoProps> = ({ className = '', style = {} }) => {
    return (
        <div
            className={`flex items-center gap-2 ${className}`}
            style={style}
        >
            <span className="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-primary-foreground">
                <MessageCircle className="h-4.5 w-4.5" aria-hidden="true" />
            </span>
            <span className="text-lg font-semibold tracking-tight text-foreground">
                Wha<span className="text-primary">Order</span>
            </span>
        </div>
    );
};

export default Logo;
